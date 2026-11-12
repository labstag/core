<?php

declare(strict_types=1);

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class MovieMessage
{
    public function __construct(
        private string $movie,
    )
    {
    }

    public function getMovie(): string
    {
        return $this->movie;
    }
}
