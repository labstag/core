<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class GameMessage
{
    public function __construct(
        private string $game,
    )
    {
    }

    public function getGame(): string
    {
        return $this->game;
    }
}
