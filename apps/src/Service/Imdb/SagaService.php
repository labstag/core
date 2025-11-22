<?php

namespace Labstag\Service\Imdb;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Labstag\Message\SagaMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;
use Labstag\Service\FileService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\MessageBusInterface;

final class SagaService
{

    private ?array $jsonTmdb = null;

    public function __construct(
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
        private LoggerInterface $logger,
        private RecommendationService $recommendationService,
        private AdminUrlGenerator $adminUrlGenerator,
        private MessageBusInterface $messageBus,
        private SagaRepository $sagaRepository,
        private MovieRepository $movieRepository,
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getAllRecommendations(): array
    {
        $sagas           = $this->sagaRepository->findAll();
        $recommendations = [];
        foreach ($sagas as $saga) {
            $result          = $this->theMovieDbApi->getDetailsSaga($saga);
            $recommendations = $this->setJsonRecommendations($result, $recommendations);
        }

        return $recommendations;
    }

    public function getSaga(array $data): Saga
    {
        $saga = $this->sagaRepository->findOneBy(
            [
                'tmdb' => $data['id'],
            ]
        );
        if (!$saga instanceof Saga) {
            $saga = new Saga();
            $saga->setEnable(true);
            $saga->setTitle($this->setName($data['name']));
            $saga->setTmdb($data['id']);
            $this->sagaRepository->save($saga);
            $this->messageBus->dispatch(new SagaMessage($saga->getId()));
        }

        return $saga;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSagaForForm(): array
    {
        $data  = $this->sagaRepository->findAllByTypeMovieEnable();
        $sagas = [];
        foreach ($data as $saga) {
            $movies = $saga->getMovies();
            if (1 === count($movies)) {
                continue;
            }

            $sagas[$saga->getTitle()] = $saga->getSlug();
        }

        return $sagas;
    }

    public function recommendations(Saga $saga, array $recommendations = []): array
    {
        $jsonRecommendations = $this->theMovieDbApi->getDetailsSaga($saga);

        return $this->setJsonRecommendations($jsonRecommendations, $recommendations);
    }

    public function update(Saga $saga): bool
    {
        $details  = $this->theMovieDbApi->getDetailsSaga($saga);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $this->sagaRepository->delete($saga);
            $this->logger->error('Saga not found TMDB id ' . $saga->getTmdb());

            return false;
        }

        $statuses = [
            $this->updateRecommendations($saga, $details),
            $this->updateSaga($saga, $details),
            $this->updateImagePoster($saga, $details),
            $this->updateImageBackdrop($saga, $details),
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

    private function setJsonRecommendations(?array $json, array $recommendations = []): array
    {
        if (!is_array($json) || !isset($json['tmdb']['parts'])) {
            return $recommendations;
        }

        foreach ($json['tmdb']['parts'] as $recommendation) {
            $tmdb              = $recommendation['id'];
            if (isset($recommendations[$tmdb])) {
                continue;
            }

            $recommendation = $this->setRecommendation($recommendation);
            if (!is_array($recommendation)) {
                continue;
            }

            $recommendations[$tmdb] = $recommendation;
        }

        return $recommendations;
    }

    private function setName(string $name): string
    {
        $name = trim(str_replace('- Saga', '', $name));

        return trim(str_replace('- Saga', '', $name));
    }

    private function setRecommendation(array $recommendation): ?array
    {
        $tmdbs            = $this->getAllJsonTmdb();
        $tmdb             = $recommendation['id'];
        if (in_array($tmdb, $tmdbs)) {
            return null;
        }

        $recommendation['poster_path'] = $this->theMovieDbApi->images()->getPosterUrl(
            $recommendation['poster_path'] ?? ''
        );
        $recommendation['backdrop_path'] = $this->theMovieDbApi->images()->getBackdropUrl(
            $recommendation['backdrop_path'] ?? ''
        );
        $recommendation['links'] = 'https://www.themoviedb.org/movie/' . $recommendation['id'];
        $recommendation['add']   = $this->urlAddWithTmdb('addWithTmdb', $recommendation);
        if ('' === $recommendation['release_date']) {
            return null;
        }

        $recommendation['date'] = new DateTime($recommendation['release_date']);
        if ($recommendation['date'] > new DateTime()) {
            return null;
        }

        return $recommendation;
    }

    private function updateImageBackdrop(Saga $saga, array $data): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($data['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'backdrop_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($backdrop));
            $this->fileService->setUploadedFile($tempPath, $saga, 'backdropFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateImagePoster(Saga $saga, array $data): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($data['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $saga, 'posterFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateRecommendations(Saga $saga, array $details): bool
    {
        $recommandations = $details['tmdb']['parts'] ?? null;
        if (is_null($recommandations) || !is_array($recommandations)) {
            foreach ($saga->getRecommendations() as $recommendation) {
                $saga->removeRecommendation($recommendation);
            }
        }

        $this->recommendationService->setRecommendations($saga, $recommandations);

        return true;
    }

    private function updateSaga(Saga $saga, array $details): bool
    {
        $saga->setTitle($this->setName($details['tmdb']['name']));
        $saga->setDescription($details['tmdb']['overview']);

        return true;
    }

    private function urlAddWithTmdb(string $type, array $data): string
    {
        $movie = new Movie();
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
