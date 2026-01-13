<?php

namespace Labstag\MessageHandler;

use Labstag\Message\SeasonAllMessage;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SeasonRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SeasonAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private SeasonRepository $seasonRepository,
    )
    {
    }

    public function __invoke(SeasonAllMessage $seasonAllMessage): void
    {
        unset($seasonAllMessage);
        $seasons                          = $this->seasonRepository->findAll();
        foreach ($seasons as $season) {
            $this->messageDispatcherService->dispatch(new SeasonMessage($season->getId()));
        }
    }
}
