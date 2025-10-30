<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Season;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SeasonRepository;
use Labstag\Service\SeasonService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SeasonMessageHandler
{
    public function __construct(
        private SeasonService $seasonService,
        private SeasonRepository $seasonRepository,
    )
    {
    }

    public function __invoke(SeasonMessage $seasonMessage): void
    {
        $seasonId = $seasonMessage->getSeason();

        $season = $this->seasonRepository->find($seasonId);
        if (!$season instanceof Season) {
            return;
        }

        $this->seasonService->update($season);
        $this->seasonRepository->save($season);
    }
}
