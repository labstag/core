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
        $seasons                          = $this->seasonRepository->findAll();
        foreach ($seasons as $season) {
            $this->messageBus->dispatch(new SeasonMessage($season->getId()));
        }
    }
}
