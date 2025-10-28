<?php

namespace Labstag\Service;

use Exception;
use Labstag\Api\TmdbApi;
use Labstag\Entity\Saga;
use Labstag\Message\SagaMessage;
use Labstag\Repository\SagaRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SagaService
{
    public function __construct(
        private LoggerInterface $logger,
        protected MessageBusInterface $messageBus,
        protected SagaRepository $sagaRepository,
        protected FileService $fileService,
        private TmdbApi $tmdbApi,
    )
    {
    }

    public function getSagaByTmdbId(string $tmdbId): Saga
    {
        $saga = $this->sagaRepository->findOneBy(
            ['tmdb' => $tmdbId]
        );
        if (!$saga instanceof Saga) {
            $saga = new Saga();
            $saga->setEnable(true);
            $saga->setTitle($tmdbId);
            $saga->setTmdb($tmdbId);
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
        $details = $this->tmdbApi->getSagaDetails($saga->getTmdb());
        if (is_null($details)) {
            $this->logger->error('Saga not found TMDB id ' . $saga->getTmdb());

            return false;
        }

        $saga->setTitle($details['name']);
        $saga->setDescription($details['overview']);
        $this->updateImageSaga($saga, $details);

        // Update logic here
        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgSaga(array $data): string
    {
        if (isset($data['poster_path'])) {
            return $this->tmdbApi->getImgw300h450($data['poster_path']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageSaga(Saga $saga, array $details): bool
    {
        $poster = $this->getImgSaga($details);
        if ('' === $poster) {
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
}
