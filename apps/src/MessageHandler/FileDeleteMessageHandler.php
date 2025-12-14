<?php

namespace Labstag\MessageHandler;

use Labstag\Message\FileDeleteMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FileDeleteMessageHandler
{
    public function __invoke(FileDeleteMessage $fileDeleteMessage): void
    {
        $filePath = $fileDeleteMessage->getFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
