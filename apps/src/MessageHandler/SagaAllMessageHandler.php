<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SagaAllMessage;
use Labstag\Message\SagaMessage;
use Labstag\Repository\SagaRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SagaAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageBus,
        private SagaRepository $sagaRepository,
    )
    {
    }

    public function __invoke(SagaAllMessage $sagaAllMessage): void
    {
        unset($sagaAllMessage);
        $sagas                           = $this->sagaRepository->findAll();
        foreach ($sagas as $saga) {
            $this->messageBus->dispatch(new SagaMessage($saga->getId()));
        }
    }
}
