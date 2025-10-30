<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class SeasonMessage
{
    public function __construct(
        private string $season,
    )
    {
    }

    public function getSeason(): string
    {
        return $this->season;
    }
}
