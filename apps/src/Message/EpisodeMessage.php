<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class EpisodeMessage
{
    public function __construct(
        private string $episode,
    )
    {
    }

    public function getEpisode(): string
    {
        return $this->episode;
    }
}
