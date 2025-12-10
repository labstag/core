<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SerieMessage;
use Labstag\Message\UpdateSerieMessage;
use Labstag\Repository\SerieRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class UpdateSerieMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
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
            $this->messageBus->dispatch(new SerieMessage($serie->getId()));
        }
    }
}
