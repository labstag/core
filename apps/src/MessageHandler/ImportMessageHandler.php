<?php

namespace Labstag\MessageHandler;

use Labstag\Message\AddMovieMessage;
use Labstag\Message\AddSerieMessage;
use Labstag\Message\ImportMessage;
use Labstag\Message\SearchGameMessage;
use Labstag\Service\FileService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class ImportMessageHandler
{
    public function __construct(
        private FileService $fileService,
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(ImportMessage $importMessage): void
    {
        $fileName = $importMessage->getFile();
        $type     = $importMessage->getType();
        $data     = $importMessage->getData();

        $file      = $this->fileService->getFileInAdapter('private', $fileName);
        $mimeType  = mime_content_type($file);
        $extension = pathinfo((string) $file, PATHINFO_EXTENSION);
        match ($mimeType) {
            'text/csv' => $this->importCsvFile($file, $data, $type),
            'text/xml' => $this->importXmlFile($file, $data, $type),
            default    => match ($extension) {
                'csv'   => $this->importCsvFile($file, $data, $type),
                'xml'   => $this->importXmlFile($file, $data, $type),
                default => false,
            },
        };
    }

    private function importCsvFile(string $path, array $data, string $type): void
    {
        $delimiter = match ($type) {
            'game'  => ',',
            'serie' => ';',
            'movie' => ';',
            default => ',',
        };

        $data = $this->fileService->getimportCsvFile($path, $delimiter);

        foreach ($data as $row) {
            $message = match ($type) {
                'game'  => new SearchGameMessage($row, 'game', $data['platform'] ?? ''),
                'serie' => new AddSerieMessage($row),
                'movie' => new AddMovieMessage($row),
                default => null,
            };

            if (!is_null($message)) {
                $this->messageBus->dispatch($message);
            }
        }
    }

    private function importXmlFile(string $path, array $data, string $type): void
    {
        $data = $this->fileService->getimportXmlFile($path);
        foreach ($data as $row) {
            $message = match ($type) {
                'game'  => new SearchGameMessage($row, 'game', $data['platform'] ?? ''),
                'serie' => new AddSerieMessage($row),
                'movie' => new AddMovieMessage($row),
                default => null,
            };

            if (!is_null($message)) {
                $this->messageBus->dispatch($message);
            }
        }
    }
}
