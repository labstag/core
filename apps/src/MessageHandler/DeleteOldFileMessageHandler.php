<?php

namespace Labstag\MessageHandler;

use Labstag\Message\DeleteOldFileMessage;
use Labstag\Service\FileService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteOldFileMessageHandler
{
    public function __construct(
        private FileService $fileService,
    )
    {
    }

    public function __invoke(DeleteOldFileMessage $deleteOldFileMessage): void
    {
        unset($deleteOldFileMessage);
        $this->fileService->deletedFileByEntities();
    }
}
