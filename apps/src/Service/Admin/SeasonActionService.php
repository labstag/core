<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Message\SeasonAllMessage;
use Labstag\Message\SeasonMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class SeasonActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new SeasonAllMessage());
    }

    public function updateSeason(int $seasonId): void
    {
        $this->messageBus->dispatch(new SeasonMessage($seasonId));
    }
}
