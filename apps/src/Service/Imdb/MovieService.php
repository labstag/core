<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieCategory;
use Labstag\Repository\MovieRepository;
use Labstag\Service\CategoryService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\Request;

final class MovieService
{

    /**
     * @var array<string, mixed>
     */
    private array $country = [];

    /**
     * @var array<string, mixed>
     */
    private array $year = [];

    public function __construct(
        private ConfigurationService $configurationService,
        private RecommendationService $recommendationService,
        private FileService $fileService,
        private CompanyService $companyService,
        private CategoryService $categoryService,
        private SagaService $sagaService,
        private EntityManagerInterface $entityManager,
        private MovieRepository $movieRepository,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCountryForForm(): array
    {
        if ([] !== $this->country) {
            return $this->country;
        }

        $entityRepository = $this->entityManager->getRepository(Movie::class);

        $country    = $entityRepository->findAllUniqueCountries();

        $this->country = $country;

        return $country;
    }

    public function getMovieApi(Request $request, int $page = 1): array
    {
        $movies             = [];
        $all                = $request->request->all();
        $tmdbs              = $this->movieRepository->getAllTmdb();
        $search             = '';
        if (isset($all['movie']['title'])) {
            $search = $all['movie']['title'];
        }

        $locale             = $this->configurationService->getLocaleTmdb();
        $results            = $this->theMovieDbApi->movies()->search(searchQuery: $search, page: $page, language: $locale);
        if (isset($results['results'])) {
            $movies = $results['results'];
            foreach ($movies as &$movie) {
                $movie['release_date'] = empty($movie['release_date']) ? null : new DateTime(
                    $movie['release_date']
                );
                $movie['poster_path']    = $this->theMovieDbApi->images()->getPosterUrl(
                    $movie['poster_path'] ?? '',
                    100
                );
            }
        }

        return array_filter($movies, fn (array $movie): bool => !in_array($movie['id'], $tmdbs));
    }

    /**
     * @return array<string, mixed>
     */
    public function getYearForForm(): array
    {
        if ([] !== $this->year) {
            return $this->year;
        }

        $entityRepository = $this->entityManager->getRepository(Movie::class);

        $data = $entityRepository->findAllUniqueYear();
        $year = [];
        foreach ($data as $value) {
            $year[$value] = $value;
        }

        $this->year = $year;

        return $year;
    }

    public function update(Movie $movie): bool
    {
        $entityRepository = $this->entityManager->getRepository(Movie::class);
        $details          = $this->theMovieDbApi->getDetailsMovie($movie);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $entityRepository->delete($movie);

            return false;
        }

        $statuses = [
            $this->updateRecommendations($movie, $details),
            $this->updateMovie($movie, $details),
            $this->updateOther($movie, $details),
            $this->updateImagePoster($movie, $details),
            $this->updateImageBackdrop($movie, $details),
            $this->updateSaga($movie, $details),
            $this->updateCategory($movie, $details),
            $this->updateCompany($movie, $details),
            $this->updateTrailer($movie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @param array<string, mixed> $details
     */
    private function setCertification(array $details, Movie $movie): void
    {
        if (!isset($details['release_dates']['results']) || 0 === count($details['release_dates']['results'])) {
            return;
        }

        foreach ($details['release_dates']['results'] as $result) {
            if ('FR' != $result['iso_3166_1']) {
                continue;
            }

            foreach ($result['release_dates'] as $release) {
                if ('' === (string) $release['certification']) {
                    continue;
                }

                $movie->setCertification((string) $release['certification']);

                return;
            }
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateCategory(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        foreach ($movie->getCategories() as $category) {
            $movie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title    = trim((string) $genre['name']);
            $category = $this->categoryService->getType($title, MovieCategory::class);
            $movie->addCategory($category);
        }

        return true;
    }

    private function updateCompany(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb']['production_companies']) || 0 === count($details['tmdb']['production_companies'])) {
            return false;
        }

        foreach ($movie->getCompanies() as $company) {
            $movie->removeCompany($company);
        }

        foreach ($details['tmdb']['production_companies'] as $company) {
            $company = $this->companyService->getCompany($company);
            $movie->addCompany($company);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageBackdrop(Movie $movie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $movie->setBackdropFile();
            $movie->setBackdrop(null);

            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'backdrop_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($backdrop));
            $this->fileService->setUploadedFile($tempPath, $movie, 'backdropFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImagePoster(Movie $movie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $movie->setPosterFile();
            $movie->setPoster(null);

            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $movie, 'posterFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateMovie(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $movie->setSlug(null);
        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $movie->setAdult($adult);
        $movie->setTitle((string) $details['tmdb']['title']);

        $this->setCertification($details, $movie);

        $tagline = (string) $details['tmdb']['tagline'];
        if ('' !== $tagline && '0' !== $tagline) {
            $movie->setCitation($tagline);
        }

        $overview = (string) $details['tmdb']['overview'];
        if ('' !== $overview && '0' !== $overview) {
            $movie->setDescription($overview);
        }

        $voteAverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $movie->setEvaluation($voteAverage);
        $movie->setVotes($voteCount);

        $movie->setCountries($details['tmdb']['origin_country']);

        $movie->setTmdb($details['tmdb']['id']);

        $movie->setReleaseDate(new DateTime($details['tmdb']['release_date']));
        $movie->setDuration((int) $details['tmdb']['runtime']);

        return true;
    }

    private function updateOther(Movie $movie, array $details): bool
    {
        if (!isset($details['other']) || is_null($details['other'])) {
            return false;
        }

        $movie->setImdb((string) $details['other']['imdb_id']);

        return true;
    }

    private function updateRecommendations(Movie $movie, array $details): bool
    {
        $this->recommendationService->setRecommendations($movie, $details['recommendations']['results'] ?? null);
        $this->recommendationService->setRecommendations($movie, $details['similar']['results'] ?? null);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateSaga(Movie $movie, array $details): bool
    {
        if (is_null($details['collection'])) {
            return false;
        }

        $saga   = $this->sagaService->getSaga($details['collection']);

        $movie->setSaga($saga);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateTrailer(Movie $movie, array $details): bool
    {
        if (is_null($details['videos']) || !is_array($details['videos'])) {
            return false;
        }

        $find = false;

        foreach ($details['videos']['results'] as $result) {
            if ('YouTube' == $result['site'] && 'Trailer' == $result['type']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                $movie->setTrailer($url);

                $find = true;

                break;
            }
        }

        return $find;
    }
}
