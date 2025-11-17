<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SagaAllMessage;
use Labstag\Message\SagaMessage;
use Labstag\Repository\SagaRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SagaAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SagaRepository $sagaRepository,
    )
    {
    }

    public function __invoke(SagaAllMessage $sagaAllMessage): void
    {
        $sagas                           = $this->sagaRepository->findAll();
        foreach ($sagas as $saga) {
            $this->messageBus->dispatch(new SagaMessage($saga->getId()));
        }
    }
}
