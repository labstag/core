<?php

namespace Labstag\Service;

use Exception;
use Labstag\Api\TheMovieDbApi;
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
        protected ConfigurationService $configurationService,
        protected FileService $fileService,
        protected TheMovieDbApi $theMovieDbApi,
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
        $locale   = $this->configurationService->getLocaleTmdb();
        $details  = $this->theMovieDbApi->movies()->getMovieCollection($saga->getTmdb(), $locale);
        if (is_null($details)) {
            $this->logger->error('Saga not found TMDB id ' . $saga->getTmdb());

            return false;
        }

        $name = trim(str_replace('- Saga', '', $details['name']));
        $name = trim(str_replace('- Saga', '', $name));

        $saga->setTitle($name);
        $saga->setDescription($details['overview']);
        $this->updateImageSaga($saga, $details);

        // Update logic here
        return true;
    }

    private function updateImageSaga(Saga $saga, array $data): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($data['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' != (string) $saga->getImg()) {
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
