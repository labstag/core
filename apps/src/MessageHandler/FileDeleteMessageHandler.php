<?php

namespace Labstag\MessageHandler;

use Labstag\Message\FileDeleteMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FileDeleteMessageHandler
{
    public function __invoke(FileDeleteMessage $message): void
    {
        $filePath = $message->getFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
