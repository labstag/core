<?php

namespace Labstag\Service\Imdb;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Saga;
use Labstag\Message\SagaMessage;
use Labstag\Repository\SagaRepository;
use Labstag\Service\FileService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SagaService
{
    public function __construct(
        private LoggerInterface $logger,
        private RecommendationService $recommendationService,
        private MessageBusInterface $messageBus,
        private SagaRepository $sagaRepository,
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function getSaga(array $data): Saga
    {
        $saga = $this->sagaRepository->findOneBy([
                'tmdb' => $data['id'],
            ]);
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

    private function setName(string $name): string
    {
        $name = trim(str_replace('- Saga', '', $name));

        return trim(str_replace('- Saga', '', $name));
    }

    private function updateImageBackdrop(Saga $saga, array $data): bool
    {
        $backdrop = $this->theMovieDbApi->images()
            ->getBackdropUrl($data['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $saga->setBackdropFile();
            $saga->setBackdrop(null);

            return false;
        }

        $this->fileService->setUploadedFile($backdrop, $saga, 'backdropFile');

        return true;
    }

    private function updateImagePoster(Saga $saga, array $data): bool
    {
        $poster = $this->theMovieDbApi->images()
            ->getPosterUrl($data['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $saga->setPosterFile();
            $saga->setPoster(null);

            return false;
        }

        $this->fileService->setUploadedFile($poster, $saga, 'posterFile');

        return true;
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
}
