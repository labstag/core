<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SerieAllMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SerieAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SerieRepository $serieRepository,
    )
    {
    }

    public function __invoke(SerieAllMessage $serieAllMessage): void
    {
        unset($serieAllMessage);
        $series                          = $this->serieRepository->findAll();
        foreach ($series as $serie) {
            $this->messageBus->dispatch(new SerieMessage($serie->getId()));
        }
    }
}
