<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class StoryMessage
{
    public function __construct(
        private string $data,
    )
    {
    }

    public function getData(): string
    {
        return $this->data;
    }
}
