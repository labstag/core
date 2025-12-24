<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class MetaMessage
{
    public function __construct(
        public readonly string $type,
        public readonly string $entity = ''
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }
}
