<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SerieMessage;
use Labstag\Message\UpdateSerieMessage;
use Labstag\Repository\SerieRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateSerieMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private SerieRepository $serieRepository,
    )
    {
    }

    public function __invoke(UpdateSerieMessage $updateSerieMessage): void
    {
        unset($updateSerieMessage);
        $series = $this->serieRepository->findBy(
            ['inProduction' => true]
        );
        foreach ($series as $serie) {
            $this->messageDispatcherService->dispatch(new SerieMessage($serie->getId()));
        }
    }
}
