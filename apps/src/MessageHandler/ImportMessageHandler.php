<?php

namespace Labstag\MessageHandler;

use Labstag\Message\ImportMessage;
use Labstag\Service\Igdb\GameService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportMessageHandler
{
    public function __construct(
        protected GameService $gameService
    )
    {
    }

    public function __invoke(ImportMessage $message): void
    {
        $file = $message->getFile();
        $type = $message->getType();
        $data = $message->getData();
        match ($type) {
            'game' => $this->importGame($file, $data),
            default => null,
        };
    }

    private function importGame(string $file, array $data): void
    {
        $this->gameService->importFile($file, $data['platform'] ?? '');
    }
}
