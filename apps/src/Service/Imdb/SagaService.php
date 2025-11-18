<?php

namespace Labstag\Service\Imdb;

use Exception;
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
        private MessageBusInterface $messageBus,
        private SagaRepository $sagaRepository,
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
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
            $saga->setTitle($this->setName($data));
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
            $this->updateSaga($saga, $details),
            $this->updateImageSaga($saga, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function setName(array $data): string
    {
        $name = trim(str_replace('- Saga', '', $data['name']));

        return trim(str_replace('- Saga', '', $name));
    }

    private function updateImageSaga(Saga $saga, array $data): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($data['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' !== (string) $saga->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $saga, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateSaga(Saga $saga, array $details): bool
    {
        $saga->setTitle($this->setName($details));
        $saga->setDescription($details['overview']);
        $saga->setJson($details);

        return true;
    }
}
