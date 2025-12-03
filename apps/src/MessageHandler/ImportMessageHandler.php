<?php

namespace Labstag\MessageHandler;

use Labstag\Message\ImportMessage;
use Labstag\Service\FileService;
use Labstag\Service\Igdb\GameService;
use Labstag\Service\Imdb\MovieService;
use Labstag\Service\Imdb\SerieService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportMessageHandler
{
    public function __construct(
        private GameService $gameService,
        private MovieService $movieService,
        private SerieService $serieService,
        private FileService $fileService,
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
            'serie' => $this->importSerie($file),
            'movie' => $this->importMovie($file),
            default => null,
        };

        $file = $this->fileService->getFileInAdapter('private', $file);
        unlink($file);
    }

    private function importGame(string $file, array $data): void
    {
        $this->gameService->importFile($file, $data['platform'] ?? '');
    }

    private function importMovie(string $file): void
    {
        $this->movieService->importFile($file);
    }

    private function importSerie(string $file): void
    {
        $this->serieService->importFile($file);
    }
}
