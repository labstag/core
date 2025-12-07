<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Game;
use Labstag\Message\GameMessage;
use Labstag\Repository\GameRepository;
use Labstag\Service\Igdb\GameService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GameMessageHandler
{
    public function __construct(
        private GameService $gameService,
        private GameRepository $gameRepository,
    )
    {
    }

    public function __invoke(GameMessage $message): void
    {
        $gameId = $message->getGame();
        $game   = $this->gameRepository->find($gameId);
        if (!$game instanceof Game) {
            return;
        }

        $this->gameService->update($game);
        $this->gameRepository->save($game);
    }
}
