<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Message\ImportMessage;
use Labstag\Message\MovieAllMessage;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MovieActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private MovieRepository $movieRepository,
        private ConfigurationService $configurationService,
        private TheMovieDbApi $theMovieDbApi,
        private TranslatorInterface $translator,
        private FileService $fileService,
    )
    {
    }

    public function addByApi(string $tmdbId): JsonResponse
    {
        $movie = $this->movieRepository->findOneBy(
            ['tmdb' => $tmdbId]
        );
        if ($movie instanceof Movie) {
            $message = new TranslatableMessage('Movie already exists');

            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $locale = $this->configurationService->getLocaleTmdb();
        $tmdb   = $this->theMovieDbApi->movies()->getDetails($tmdbId, $locale);
        if (is_null($tmdb)) {
            $message = new TranslatableMessage(
                'The movie with the TMDB id %id% does not exist',
                ['%id%' => $tmdbId]
            );

            return new JsonResponse(
                [
                    'status'  => 'error',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $other = $this->theMovieDbApi->movies()->getMovieExternalIds($tmdbId);
        if (!isset($other['imdb_id'])) {
            $message = new TranslatableMessage('No Imdb id for this movie');

            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $movie = new Movie();
        $movie->setEnable(true);
        $movie->setAdult(false);
        $movie->setFile(false);
        $movie->setTmdb($tmdbId);
        $movie->setImdb($other['imdb_id']);
        $movie->setTitle($tmdb['title']);

        $this->movieRepository->save($movie);
        $this->messageBus->dispatch(new MovieMessage($movie->getId()));
        $message = new TranslatableMessage('Movie is being added');

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

    public function importFileMovie(?UploadedFile $file): JsonResponse
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
        $this->messageBus->dispatch(new ImportMessage($filename, 'movie', []));

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function jsonMovie(Movie $movie): array
    {
        return $this->theMovieDbApi->getDetailsMovie($movie);
    }

    public function tmdbUrl(string $tmdbId): string
    {
        return 'https://www.themoviedb.org/movie/' . $tmdbId;
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new MovieAllMessage());
    }

    public function updateMovie(int $movieId): void
    {
        $this->messageBus->dispatch(new MovieMessage($movieId));
    }
}
