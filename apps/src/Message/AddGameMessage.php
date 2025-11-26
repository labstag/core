<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
final class AddGameMessage
{
    public function __construct(
        private string $id,
        private string $type,
        private string $platform = '',
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
