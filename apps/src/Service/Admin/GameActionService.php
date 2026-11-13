<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Service\FileService;
use Labstag\Service\Igdb\Game\GameApiService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Labstag\Message\ImportMessage;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Message\AddGameMessage;
use Labstag\Message\GameAllMessage;
use Labstag\Message\GameMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GameActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private TranslatorInterface $translator,
        private TheMovieDbApi $theMovieDbApi,
        private FileService $fileService,
        private GameApiService $gameApiService,
    )
    {
    }

    public function addByApi(string $id, string $platform): JsonResponse
    {
        $this->messageBus->dispatch(new AddGameMessage($id, 'game', $platform));

        $translatableMessage = new TranslatableMessage('Game is being added');

        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $id,
                'message' => $this->translator->trans($translatableMessage->getMessage(), $translatableMessage->getParameters()),
            ]
        );
    }

    public function igdbUrl(string $igdbId): string
    {
        return 'https://www.igdb.com/games/' . $igdbId;
    }

    public function importFileGame(?UploadedFile $file, string $platform): JsonResponse
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
        $this->messageBus->dispatch(
            new ImportMessage(
                $filename,
                'game',
                ['platform' => $platform]
            )
        );

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function jsonGame(string $igdbId): ?array
    {
        return $this->gameApiService->getApiGameId($igdbId);
    }

    public function jsonMovie(Movie $movie): array
    {
        return $this->theMovieDbApi->getDetailsMovie($movie);
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new GameAllMessage());
    }

    public function updateGame(int $gameId): void
    {
        $this->messageBus->dispatch(new GameMessage($gameId));
    }
}
