<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SagaAllMessage;
use Labstag\Message\SagaMessage;
use Labstag\Repository\SagaRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SagaAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private SagaRepository $sagaRepository,
    )
    {
    }

    public function __invoke(SagaAllMessage $sagaAllMessage): void
    {
        unset($sagaAllMessage);
        $sagas                           = $this->sagaRepository->findAll();
        foreach ($sagas as $saga) {
            $this->messageDispatcherService->dispatch(new SagaMessage($saga->getId()));
        }
    }
}
