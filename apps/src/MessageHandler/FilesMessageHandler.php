<?php

namespace Labstag\MessageHandler;

use Labstag\Message\FilesMessage;
use Labstag\Service\FileService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FilesMessageHandler
{
    public function __construct(
        protected FileService $fileService,
    )
    {
    }

    public function __invoke(FilesMessage $message): void
    {
        unset($message);
        $total = $this->fileService->deletedFileByEntities();
    }
}
