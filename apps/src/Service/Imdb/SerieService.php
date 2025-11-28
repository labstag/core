<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\SerieCategory;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SerieRepository;
use Labstag\Service\CategoryService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

final class SerieService
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
        private RecommendationService $recommendationService,
        private MessageBusInterface $messageBus,
        private ConfigurationService $configurationService,
        private FileService $fileService,
        private CompanyService $companyService,
        private SeasonService $seasonService,
        private SerieRepository $serieRepository,
        private CategoryService $categoryService,
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

        $country    = $this->serieRepository->findAllUniqueCountries();

        $this->country = $country;

        return $country;
    }

    public function getSerieApi(Request $request, int $page = 1): array
    {
        $series             = [];
        $all                = $request->request->all();
        $tmdbs              = $this->serieRepository->getAllTmdb();
        $search             = '';
        if (isset($all['serie']['imdb'])) {
            $results = $this->theMovieDbApi->other()->findByImdb($all['serie']['imdb']);
            if (isset($results['tv_results'])) {
                $series = $results['tv_results'];
                foreach ($series as &$serie) {
                    $serie['first_air_date'] = empty($serie['first_air_date']) ? null : new DateTime(
                        $serie['first_air_date']
                    );
                    $serie['poster_path']    = $this->theMovieDbApi->images()->getPosterUrl(
                        $serie['poster_path'] ?? '',
                        100
                    );
                }
            }

            return array_filter($series, fn (array $serie): bool => !in_array($serie['id'], $tmdbs));
        }
        
        
        if (isset($all['serie']['title'])) {
            $search = $all['serie']['title'];
        }

        $locale             = $this->configurationService->getLocaleTmdb();
        $results            = $this->theMovieDbApi->tvserie()->search(searchQuery: $search, page: $page, language: $locale);
        if (isset($results['results'])) {
            $series = $results['results'];
            foreach ($series as &$serie) {
                $serie['first_air_date'] = empty($serie['first_air_date']) ? null : new DateTime(
                    $serie['first_air_date']
                );
                $serie['poster_path']    = $this->theMovieDbApi->images()->getPosterUrl(
                    $serie['poster_path'] ?? '',
                    100
                );
            }
        }

        return array_filter($series, fn (array $serie): bool => !in_array($serie['id'], $tmdbs));
    }

    /**
     * @return mixed[]
     */
    public function getSeriesChoice(): array
    {
        $series = $this->serieRepository->findBy(
            [],
            ['title' => 'ASC']
        );
        $choices = [];
        /** @var Serie $serie */
        foreach ($series as $serie) {
            $label           = $serie->getTitle();
            $id              = $serie->getId();
            $choices[$label] = $id;
        }

        return $choices;
    }

    /**
     * @return array<string, mixed>
     */
    public function getYearForForm(): array
    {
        if ([] !== $this->year) {
            return $this->year;
        }

        $data = $this->serieRepository->findAllUniqueYear();
        $year = [];
        foreach ($data as $value) {
            $year[$value] = $value;
        }

        $this->year = $year;

        return $year;
    }

    public function update(Serie $serie): bool
    {
        $details  = $this->theMovieDbApi->getDetailsSerie($serie);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $this->serieRepository->delete($serie);

            return false;
        }

        $statuses = [
            $this->updateSerie($serie, $details),
            $this->updateRecommendations($serie, $details),
            $this->updateOther($serie, $details),
            $this->setCertification($details, $serie),
            $this->setCitation($serie, $details),
            $this->setDescription($serie, $details),
            $this->setReleaseDate($serie, $details),
            $this->setLastreleaseDate($serie, $details),
            $this->updateImageBackdrop($serie, $details),
            $this->updateImagePoster($serie, $details),
            $this->updateCategory($serie, $details),
            $this->updateTrailer($serie, $details),
            $this->updateCompany($serie, $details),
            $this->updateSeasons($serie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @param array<string, mixed> $details
     */
    private function setCertification(array $details, Serie $serie): bool
    {
        if (!isset($details['release_dates']['results']) || 0 === count($details['release_dates']['results'])) {
            return false;
        }

        foreach ($details['release_dates']['results'] as $result) {
            if ('FR' != $result['iso_3166_1']) {
                continue;
            }

            foreach ($result['release_dates'] as $release) {
                if ('' === (string) $release['certification']) {
                    continue;
                }

                $serie->setCertification((string) $release['certification']);

                return true;
            }
        }

        return false;
    }

    private function setCitation(Serie $serie, array $details): bool
    {
        $tagline = (string) $details['tmdb']['tagline'];
        if ('' !== $tagline && '0' !== $tagline) {
            $serie->setCitation($tagline);
        }

        return true;
    }

    private function setDescription(Serie $serie, array $details): bool
    {
        $overview = (string) $details['tmdb']['overview'];
        if ('' !== $overview && '0' !== $overview) {
            $serie->setDescription($overview);
        }

        return true;
    }

    private function setLastreleaseDate(Serie $serie, array $details): bool
    {
        $lastReleaseDate = (is_null(
            $details['tmdb']['last_air_date']
        ) || empty($details['tmdb']['last_air_date'])) ? null : new DateTime($details['tmdb']['last_air_date']);
        $serie->setLastreleaseDate($lastReleaseDate);

        return true;
    }

    private function setReleaseDate(Serie $serie, array $details): bool
    {
        $releaseDate = (is_null(
            $details['tmdb']['first_air_date']
        ) || empty($details['tmdb']['first_air_date'])) ? null : new DateTime($details['tmdb']['first_air_date']);
        $serie->setReleaseDate($releaseDate);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateCategory(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        foreach ($serie->getCategories() as $category) {
            $serie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title    = trim((string) $genre['name']);
            $category = $this->categoryService->getType($title, SerieCategory::class);

            $serie->addCategory($category);
        }

        return true;
    }

    private function updateCompany(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb']['production_companies']) || 0 === count($details['tmdb']['production_companies'])) {
            return false;
        }

        foreach ($serie->getCompanies() as $company) {
            $serie->removeCompany($company);
        }

        foreach ($details['tmdb']['production_companies'] as $company) {
            $company = $this->companyService->getCompany($company);
            $serie->addCompany($company);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageBackdrop(Serie $serie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $serie->setBackdropFile();
            $serie->setBackdrop(null);

            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'backdrop_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($backdrop));
            $this->fileService->setUploadedFile($tempPath, $serie, 'backdropFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImagePoster(Serie $serie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $serie->setPosterFile();
            $serie->setPoster(null);

            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $serie, 'posterFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateOther(Serie $serie, array $details): bool
    {
        if (!isset($details['other']) || is_null($details['other'])) {
            return false;
        }

        $serie->setImdb((string) $details['other']['imdb_id']);

        return true;
    }

    private function updateRecommendations(Serie $serie, array $details): bool
    {
        $this->recommendationService->setRecommendations($serie, $details['recommendations']['results'] ?? null);
        $this->recommendationService->setRecommendations($serie, $details['similar']['results'] ?? null);

        return true;
    }

    private function updateSeasons(Serie $serie, array $details): bool
    {
        if (isset($details['tmdb']['seasons']) && is_array($details['tmdb']['seasons'])) {
            foreach ($details['tmdb']['seasons'] as $seasonData) {
                $season = $this->seasonService->getSeason($serie, $seasonData);
                if ($season instanceof Season) {
                    $this->seasonService->save($season);
                }
            }
        }

        $seasons = $this->seasonService->getSeasons($serie);
        foreach ($seasons as $season) {
            $this->messageBus->dispatch(new SeasonMessage($season->getId()));
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateSerie(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $serie->setInProduction((bool) $details['tmdb']['in_production']);
        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $serie->setAdult($adult);
        $serie->setTitle((string) $details['tmdb']['name']);

        $voteEverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $serie->setEvaluation($voteEverage);
        $serie->setVotes($voteCount);

        $serie->setCountries($details['tmdb']['origin_country']);

        $serie->setTmdb($details['tmdb']['id']);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateTrailer(Serie $serie, array $details): bool
    {
        if (is_null($details['videos']) || !is_array($details['videos'])) {
            return false;
        }

        $find = false;

        foreach ($details['videos']['results'] as $result) {
            if ('YouTube' == $result['site'] && 'Trailer' == $result['type']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                $serie->setTrailer($url);

                $find = true;

                break;
            }
        }

        return $find;
    }
}
