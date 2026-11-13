<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\SerieCategory;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SeasonRepository;
use Labstag\Repository\SerieRepository;
use Labstag\Service\CategoryService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\Imdb\Serie\ApiService;
use Labstag\Service\Imdb\Serie\CategoryService as SerieCategoryService;
use Labstag\Service\Imdb\Serie\CompanyService as SerieCompanyService;
use Labstag\Service\Imdb\Serie\CreditService;
use Labstag\Service\Imdb\Serie\DetailService;
use Labstag\Service\Imdb\Serie\ImageService;
use Labstag\Service\MessageDispatcherService;
use Labstag\Service\VideoService;

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
        private MessageDispatcherService $messageDispatcherService,
        private ConfigurationService $configurationService,
        private EntityManagerInterface $entityManager,
        private FileService $fileService,
        private SeasonService $seasonService,
        private SerieRepository $serieRepository,
        private VideoService $videoService,
        private ApiService $apiService,
        private ImageService $imageService,
        private DetailService $detailService,
        private CreditService $creditService,
        private SerieCategoryService $categoryService,
        private SerieCompanyService $companyService,
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

    public function getSerieApi(array $data, int $page = 1): array
    {
        return $this->apiService->getSerieApi($data, $page);
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
        $details  = $this->apiService->getDetailsSerie($serie);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $this->serieRepository->delete($serie);

            return false;
        }

        $statuses = [
            $this->detailService->updateSerie($serie, $details),
            $this->detailService->updateOther($serie, $details),
            $this->detailService->setCertification($serie, $details),
            $this->detailService->setCitation($serie, $details),
            $this->detailService->setDescription($serie, $details),
            $this->detailService->setReleaseDate($serie, $details),
            $this->detailService->setLastreleaseDate($serie, $details),
            $this->imageService->updateImageBackdrop($serie, $details),
            $this->imageService->updateImagePoster($serie, $details),
            $this->categoryService->updateCategory($serie, $details),
            $this->detailService->updateTrailer($serie, $details),
            $this->companyService->updateCompany($serie, $details),
            $this->creditService->updateCredits($serie, $details),
            $this->updateSeasons($serie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function cleanupTrashSerie(Serie $serie): void
    {
        $repository = $this->entityManager->getRepository(Season::class);
        if (!$repository instanceof SeasonRepository) {
            return;
        }

        $filters = $this->entityManager->getFilters();
        if ($filters->has('softdeleteable') && $filters->isEnabled('softdeleteable')) {
            $filters->disable('softdeleteable');
        }

        $paragraphs = $repository->getdeleteTrashBySerie($serie);
        foreach ($paragraphs as $paragraph) {
            $this->entityManager->remove($paragraph);
        }

        $this->entityManager->flush();
        if ($filters->has('softdeleteable') && !$filters->isEnabled('softdeleteable')) {
            $filters->enable('softdeleteable');
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    /**
     * @param array<string, mixed> $details
     */
    /**
     * @param array<string, mixed> $details
     */
    /**
     * @param array<string, mixed> $details
     */
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
            $this->messageDispatcherService->dispatch(new SeasonMessage($season->getId()));
        }

        $this->cleanupTrashSerie($serie);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    /**
     * @param array<string, mixed> $details
     */
}
