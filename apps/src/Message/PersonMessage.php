<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class PersonMessage
{
    public function __construct(
        private string $person,
    )
    {
    }

    public function getPerson(): string
    {
        return $this->person;
    }
}
