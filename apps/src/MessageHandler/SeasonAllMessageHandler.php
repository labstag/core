<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SeasonAllMessage;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SeasonRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SeasonAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SeasonRepository $seasonRepository,
    )
    {
    }

    public function __invoke(SeasonAllMessage $seasonAllMessage): void
    {
        unset($seasonAllMessage);
        $series                          = $this->seasonRepository->findAll();
        foreach ($series as $serie) {
            $this->messageBus->dispatch(new SeasonMessage($serie->getId()));
        }
    }
}
