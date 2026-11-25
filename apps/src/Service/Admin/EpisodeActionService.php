<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Message\EpisodeMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class EpisodeActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function updateEpisode(string $episodeId): void
    {
        $this->messageBus->dispatch(new EpisodeMessage($episodeId));
    }
}
