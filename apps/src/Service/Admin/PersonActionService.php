<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Message\PersonAllMessage;
use Labstag\Message\PersonMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class PersonActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new PersonAllMessage());
    }

    public function updatePerson(int $personId): void
    {
        $this->messageBus->dispatch(new PersonMessage($personId));
    }
}
