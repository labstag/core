<?php

namespace Labstag\MessageHandler;

use Labstag\Message\ImportMessage;
use Labstag\Service\Igdb\GameService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportMessageHandler
{
    public function __construct(
        private GameService $gameService,
    )
    {
    }

    public function __invoke(ImportMessage $importMessage): void
    {
        $file = $importMessage->getFile();
        $type = $importMessage->getType();
        $data = $importMessage->getData();
        match ($type) {
            'game'  => $this->importGame($file, $data),
            default => null,
        };
        

        unlink($file);
    }

    private function importGame(string $file, array $data): void
    {
        $this->gameService->importFile($file, $data['platform'] ?? '');
    }
}
