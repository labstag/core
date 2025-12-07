<?php

namespace Labstag\MessageHandler;

use Labstag\Message\GameAllMessage;
use Labstag\Message\GameMessage;
use Labstag\Repository\GameRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class GameAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private GameRepository $gameRepository,
    )
    {
    }

    public function __invoke(GameAllMessage $message): void
    {
        unset($message);
        $games = $this->gameRepository->findAll();
        foreach ($games as $game) {
            $this->messageBus->dispatch(new GameMessage($game->getId()));
        }
    }
}
