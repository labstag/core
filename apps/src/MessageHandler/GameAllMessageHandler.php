<?php

namespace Labstag\MessageHandler;

use Labstag\Message\GameAllMessage;
use Labstag\Message\GameMessage;
use Labstag\Repository\GameRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GameAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private GameRepository $gameRepository,
    )
    {
    }

    public function __invoke(GameAllMessage $gameAllMessage): void
    {
        unset($gameAllMessage);
        $games = $this->gameRepository->findAll();
        foreach ($games as $game) {
            $this->messageDispatcherService->dispatch(new GameMessage($game->getId()));
        }
    }
}
