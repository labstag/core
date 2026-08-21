<?php

namespace Labstag\MessageHandler;

use Labstag\Message\AddGameMessage;
use Labstag\Service\IgdbService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AddGameMessageHandler
{
    public function __construct(
        private IgdbService $igdbService,
    )
    {
    }

    public function __invoke(AddGameMessage $addGameMessage): void
    {
        $id         = $addGameMessage->getId();
        $type       = $addGameMessage->getType();
        $platformId = $addGameMessage->getPlatform();
        match ($type) {
            'platform' => $this->igdbService->platforms()->addByApi($id),
            'game' => $this->igdbService->games()->addByApi($id, $platformId),
            default => null,
        };
    }
}
