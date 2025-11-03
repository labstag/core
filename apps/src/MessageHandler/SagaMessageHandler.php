<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Saga;
use Labstag\Message\SagaMessage;
use Labstag\Repository\SagaRepository;
use Labstag\Service\SagaService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SagaMessageHandler
{
    public function __construct(
        private SagaService $sagaService,
        private SagaRepository $sagaRepository,
    )
    {
    }

    public function __invoke(SagaMessage $sagaMessage): void
    {
        $sagaId = $sagaMessage->getData();
        $saga   = $this->sagaRepository->find($sagaId);
        if (!$saga instanceof Saga) {
            return;
        }

        $this->sagaService->update($saga);
        $this->sagaRepository->save($saga);
    }
}
