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
        $fileName   = $importMessage->getFile();
        $type       = $importMessage->getType();
        $data       = $importMessage->getData();
        $file       = $this->fileService->getFileInAdapter('private', $fileName);
        $fileFormat = $this->detectFileFormat($file);

        match ($fileFormat) {
            'csv'   => $this->importCsvFile($file, $data, $type),
            'xml'   => $this->importXmlFile($file, $data, $type),
            default => null,
        };
    }

    private function createMessage(string $type, array $row, array $data): ?object
    {
        return match ($type) {
            'game'  => new SearchGameMessage($row, $data['platform'] ?? ''),
            'serie' => new AddSerieMessage($row),
            'movie' => new AddMovieMessage($row),
            default => null,
        };
    }

    private function detectFileFormat(string $file): string
    {
        $mimeType  = mime_content_type($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        return match ($mimeType) {
            'text/csv' => 'csv',
            'text/xml' => 'xml',
            default    => match ($extension) {
                'csv'   => 'csv',
                'xml'   => 'xml',
                default => 'unknown',
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

        $rows = $this->fileService->getimportCsvFile($path, $delimiter);
        $newrow = [];
        foreach ($rows as $row) {
            $newrow[$row['Nom']] = $row;
        }

        foreach ($newrow as $row) {
            $message = $this->createMessage($type, $row, $data);

            if (!is_null($message)) {
                $this->messageBus->dispatch($message);
            }
        }
    }

    private function importXmlFile(string $path, array $data, string $type): void
    {
        $rows = $this->fileService->getimportXmlFile($path);
        $newrow = [];
        foreach ($rows as $row) {
            $newrow[$row['name']] = $row;
        }

        foreach ($newrow as $row) {
            $message = $this->createMessage($type, $row, $data);

            if (!is_null($message)) {
                $this->messageBus->dispatch($message);
            }
        }
    }
}
