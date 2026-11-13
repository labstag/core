<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieCategory;
use Labstag\Repository\MovieRepository;
use Labstag\Service\CategoryService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\VideoService;
use Labstag\Service\Imdb\Movie\ApiService;
use Labstag\Service\Imdb\Movie\CategoryService as MovieCategoryService;
use Labstag\Service\Imdb\Movie\CompanyService as MovieCompanyService;
use Labstag\Service\Imdb\Movie\CreditService;
use Labstag\Service\Imdb\Movie\DetailService;
use Labstag\Service\Imdb\Movie\ImageService;

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
        private FileService $fileService,
        private PersonService $personService,
        private SagaService $sagaService,
        private EntityManagerInterface $entityManager,
        private MovieRepository $movieRepository,
        private VideoService $videoService,
        private TheMovieDbApi $theMovieDbApi,
        private ApiService $apiService,
        private ImageService $imageService,
        private CreditService $creditService,
        private MovieCategoryService $movieCategoryService,
        private MovieCompanyService $movieCompanyService,
        private DetailService $detailService,
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

    public function getMovieApi(array $data, int $page = 1): array
    {
        return $this->apiService->getMovieApi($data, $page);
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
        $details          = $this->apiService->getDetailsMovie($movie);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $entityRepository->delete($movie);

            return false;
        }

        $statuses = [
            $this->detailService->updateMovie($movie, $details),
            $this->detailService->updateOther($movie, $details),
            $this->detailService->setCertification($movie, $details),
            $this->imageService->updateImagePoster($movie, $details),
            $this->imageService->updateImageBackdrop($movie, $details),
            $this->updateSaga($movie, $details),
            $this->movieCategoryService->updateCategory($movie, $details),
            $this->movieCompanyService->updateCompany($movie, $details),
            $this->creditService->updateCredits($movie, $details),
            $this->detailService->updateTrailer($movie, $details),
        ];

        return in_array(true, $statuses, true);
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
        $find  = false;
        $video = $this->videoService->getTrailer($details['videos']);
        if (!is_null($video)) {
            $movie->setTrailer($video);
            $find = true;
        }

        return $find;
    }
}
