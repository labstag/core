<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SerieAllMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SerieAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private SerieRepository $serieRepository,
    )
    {
    }

    public function __invoke(SerieAllMessage $serieAllMessage): void
    {
        unset($serieAllMessage);
        $series                          = $this->serieRepository->findAll();
        foreach ($series as $serie) {
            $this->messageDispatcherService->dispatch(new SerieMessage($serie->getId()));
        }
    }
}
