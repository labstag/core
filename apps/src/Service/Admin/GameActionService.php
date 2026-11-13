<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

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
