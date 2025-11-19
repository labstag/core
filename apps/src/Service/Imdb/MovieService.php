<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieCategory;
use Labstag\Repository\MovieRepository;
use Labstag\Service\CategoryService;
use Labstag\Service\FileService;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class MovieService
{

    /**
     * @var array<string, mixed>
     */
    private array $country = [];

    private ?array $jsonTmdb = null;

    /**
     * @var array<string, mixed>
     */
    private array $year = [];

    public function __construct(
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
        private AdminUrlGenerator $adminUrlGenerator,
        private MovieRepository $movieRepository,
        private FileService $fileService,
        private CompanyService $companyService,
        private CategoryService $categoryService,
        private SagaService $sagaService,
        private EntityManagerInterface $entityManager,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getAllRecommandations(): array
    {
        $rows = $this->entityManager->getConnection()->fetchAllAssociative('SELECT json FROM movie');

        $results         = array_column($rows, 'json');
        $recommandations = [];
        foreach ($results as $result) {
            $data            = json_decode((string) $result, true);
            $recommandations = $this->setJsonRecommandations($data, new Movie(), $recommandations);
        }

        return $recommandations;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCountryForForm(): array
    {
        if ([] !== $this->country) {
            return $this->country;
        }

        $country    = $this->movieRepository->findAllUniqueCountries();

        $this->country = $country;

        return $country;
    }

    /**
     * @return array<string, mixed>
     */
    public function getYearForForm(): array
    {
        if ([] !== $this->year) {
            return $this->year;
        }

        $data = $this->movieRepository->findAllUniqueYear();
        $year = [];
        foreach ($data as $value) {
            $year[$value] = $value;
        }

        $this->year = $year;

        return $year;
    }

    public function recommandations(Movie $movie, array $recommandations = []): array
    {
        $jsonRecommandations = $this->theMovieDbApi->getDetailsMovie($movie);

        return $this->setJsonRecommandations($jsonRecommandations, $movie, $recommandations);
    }

    public function update(Movie $movie): bool
    {
        $details  = $this->theMovieDbApi->getDetailsMovie($movie);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $this->movieRepository->delete($movie);

            return false;
        }

        $statuses = [
            $this->updateMovie($movie, $details),
            $this->updateOther($movie, $details),
            $this->updateImageMovie($movie, $details),
            $this->updateSaga($movie, $details),
            $this->updateCategory($movie, $details),
            $this->updateCompany($movie, $details),
            $this->updateTrailer($movie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function getAllJsonTmdb(): array
    {
        if (!is_null($this->jsonTmdb)) {
            return $this->jsonTmdb;
        }

        $this->jsonTmdb = $this->movieRepository->getAllJsonTmdb();

        return $this->jsonTmdb;
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

    private function setJsonRecommandations(?array $json, Movie $movie, array $recommandations = []): array
    {
        if (!is_array($json) || !isset($json['recommandations'])) {
            return $recommandations;
        }

        foreach ($json['recommandations']['results'] as $recommandation) {
            $tmdb              = $recommandation['id'];
            if (isset($recommandations[$tmdb])) {
                continue;
            }

            $recommandation = $this->setRecommandation($recommandation, $movie);
            if (!is_array($recommandation)) {
                continue;
            }

            $recommandations[$tmdb] = $recommandation;
        }

        return $recommandations;
    }

    private function setRecommandation(array $recommandation, Movie $movie): ?array
    {
        $tmdbs            = $this->getAllJsonTmdb();
        $tmdb             = $recommandation['id'];
        if (in_array($tmdb, $tmdbs)) {
            return null;
        }

        $recommandation['poster_path'] = $this->theMovieDbApi->images()->getPosterUrl(
            $recommandation['poster_path'] ?? ''
        );
        $recommandation['backdrop_path'] = $this->theMovieDbApi->images()->getBackdropUrl(
            $recommandation['backdrop_path'] ?? ''
        );
        $recommandation['links'] = 'https://www.themoviedb.org/movie/' . $recommandation['id'];
        $recommandation['add']   = $this->urlAddWithTmdb('addWithTmdb', $movie, $recommandation);
        if ('' === $recommandation['release_date']) {
            return null;
        }

        $recommandation['date'] = new DateTime($recommandation['release_date']);
        if ($recommandation['date'] > new DateTime()) {
            return null;
        }

        return $recommandation;
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
    private function updateImageMovie(Movie $movie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' !== (string) $movie->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $movie, 'imgFile');

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
        $movie->setJson($details);

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

    private function urlAddWithTmdb(string $type, Movie $movie, array $data): string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $movie::class || $movie instanceof $entityClass) {
                $url = $this->adminUrlGenerator->setController($controller::class);
                $url->set('name', $data['title'] ?? $data['name']);
                $url->set('tmdb', $data['id']);
                $url->setAction($type);

                return str_replace('http://localhost', '', $url->generateUrl());
            }
        }

        return '';
    }
}
