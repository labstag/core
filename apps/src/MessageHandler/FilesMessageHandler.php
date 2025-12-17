<?php

namespace Labstag\MessageHandler;

use Labstag\Message\FilesMessage;
use Labstag\Service\FileService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FilesMessageHandler
{
    public function __construct(
        private FileService $fileService,
    )
    {
    }

    public function __invoke(FilesMessage $filesMessage): void
    {
        unset($filesMessage);
        $this->fileService->deletedFileByEntities();
    }
}
