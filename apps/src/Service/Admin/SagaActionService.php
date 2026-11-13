<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Message\SagaAllMessage;
use Labstag\Message\SagaMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class SagaActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new SagaAllMessage());
    }

    public function updateSaga(int $sagaId): void
    {
        $this->messageBus->dispatch(new SagaMessage($sagaId));
    }
}
