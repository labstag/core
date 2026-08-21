<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class ImportMessage
{
    public function __construct(
        private string $file,
        private string $type = '',
        private array $data = [],
    )
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
