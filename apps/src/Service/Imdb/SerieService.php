<?php

namespace Labstag\Service\Imdb;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\SerieCategory;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SerieRepository;
use Labstag\Service\CategoryService;
use Labstag\Service\FileService;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\MessageBusInterface;

final class SerieService
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
        private MessageBusInterface $messageBus,
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
     * @return mixed[]
     */
    public function getAllRecommandations(): array
    {
        $series = $this->serieRepository->findAll();
        $recommandations = [];
        foreach ($series as $serie) {
            $result            = $this->theMovieDbApi->getDetailsSerie($serie);
            $recommandations = $this->setJsonRecommandations($result, $serie, $recommandations);
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

        $country    = $this->serieRepository->findAllUniqueCountries();

        $this->country = $country;

        return $country;
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

    public function recommandations(Serie $serie, array $recommandations = []): array
    {
        $jsonRecommandations = $this->theMovieDbApi->getDetailsSerie($serie);

        return $this->setJsonRecommandations($jsonRecommandations, $serie, $recommandations);
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
            $this->updateOther($serie, $details),
            $this->setCertification($details, $serie),
            $this->setCitation($serie, $details),
            $this->setDescription($serie, $details),
            $this->setReleaseDate($serie, $details),
            $this->setLastreleaseDate($serie, $details),
            $this->updateImageMovie($serie, $details),
            $this->updateCategory($serie, $details),
            $this->updateTrailer($serie, $details),
            $this->updateCompany($serie, $details),
            $this->updateSeasons($serie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function getAllJsonTmdb(): array
    {
        if (!is_null($this->jsonTmdb)) {
            return $this->jsonTmdb;
        }

        $this->jsonTmdb = $this->serieRepository->getAllJsonTmdb();

        return $this->jsonTmdb;
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

    private function setJsonRecommandations(?array $json, Serie $serie, array $recommandations = []): array
    {
        if (!is_array($json) || !isset($json['recommandations'])) {
            return $recommandations;
        }

        foreach ($json['recommandations']['results'] as $recommandation) {
            $tmdb              = $recommandation['id'];
            if (isset($recommandations[$tmdb])) {
                continue;
            }

            $recommandation = $this->setRecommandation($recommandation, $serie);
            if (!is_array($recommandation)) {
                continue;
            }

            $recommandations[$tmdb] = $recommandation;
        }

        return $recommandations;
    }

    private function setLastreleaseDate(Serie $serie, array $details): bool
    {
        $lastReleaseDate = (is_null(
            $details['tmdb']['last_air_date']
        ) || empty($details['tmdb']['last_air_date'])) ? null : new DateTime($details['tmdb']['last_air_date']);
        $serie->setLastreleaseDate($lastReleaseDate);

        return true;
    }

    private function setRecommandation(array $recommandation, Serie $serie): ?array
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
        $recommandation['links'] = 'https://www.themoviedb.org/tv/' . $recommandation['id'];
        $recommandation['add']   = $this->urlAddWithTmdb('addWithTmdb', $serie, $recommandation);
        if ('' === $recommandation['first_air_date']) {
            return null;
        }

        $recommandation['date'] = new DateTime($recommandation['first_air_date']);
        if ($recommandation['date'] > new DateTime()) {
            return null;
        }

        return $recommandation;
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
    private function updateImageMovie(Serie $serie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' !== (string) $serie->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $serie, 'imgFile');

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

    private function urlAddWithTmdb(string $type, Serie $serie, array $data): string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $serie::class || $serie instanceof $entityClass) {
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
