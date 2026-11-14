<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Serie;
use Labstag\Message\ImportMessage;
use Labstag\Message\SerieAllMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SerieActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SerieRepository $serieRepository,
        private ConfigurationService $configurationService,
        private TheMovieDbApi $theMovieDbApi,
        private TranslatorInterface $translator,
        private FileService $fileService,
    )
    {
    }

    public function addByApi(string $tmdbId): JsonResponse
    {
        $serie = $this->serieRepository->findOneBy(
            ['tmdb' => $tmdbId]
        );
        if ($serie instanceof Serie) {
            $message = new TranslatableMessage('Serie already exists');
            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $locale = $this->configurationService->getLocaleTmdb();
        $tmdb   = $this->theMovieDbApi->tvserie()->getDetails($tmdbId, $locale);
        if (is_null($tmdb)) {
            $message = new TranslatableMessage(
                'The series with the TMDB id %id% does not exist',
                ['%id%' => $tmdbId]
            );
            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $other = $this->theMovieDbApi->tvserie()->getTvExternalIds($tmdbId);
        if (!isset($other['imdb_id'])) {
            $message = new TranslatableMessage('No Imdb id for this series');
            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $serie = new Serie();
        $serie->setFile(false);
        $serie->setEnable(true);
        $serie->setAdult(false);
        $serie->setTmdb($tmdbId);
        $serie->setImdb($other['imdb_id']);
        $serie->setTitle($tmdb['name']);

        $this->serieRepository->save($serie);
        $this->messageBus->dispatch(new SerieMessage($serie->getId()));
        $message = new TranslatableMessage('Serie is being added');

        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $tmdbId,
                'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
            ]
        );
    }

    public function imdbUrl(string $imdbId): string
    {
        return 'https://www.imdb.com/title/' . $imdbId;
    }

    public function importFileSerie(?UploadedFile $file): JsonResponse
    {
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => 'No file uploaded',
                ]
            );
        }

        $content   = file_get_contents($file->getPathname());
        $extension = $file->getClientOriginalExtension();
        $filename  = uniqid('import_', true) . '.' . $extension;
        $this->fileService->saveFileInAdapter('private', $filename, $content);
        $this->messageBus->dispatch(new ImportMessage($filename, 'serie', []));

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function jsonSerie(Serie $serie): array
    {
        return $this->theMovieDbApi->getDetailsSerie($serie);
    }

    public function tmdbUrl(string $tmdbId): string
    {
        return 'https://www.themoviedb.org/tv/' . $tmdbId;
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new SerieAllMessage());
    }

    public function updateSerie(int $serieId): void
    {
        $this->messageBus->dispatch(new SerieMessage($serieId));
    }
}
