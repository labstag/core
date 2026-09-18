<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class SearchGameMessage
{
    public function __construct(
        private array $data,
        private string $platform = '',
    )
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }
}
