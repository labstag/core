<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class FileDeleteMessage
{
    public function __construct(
        private string $filePath,
    )
    {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
