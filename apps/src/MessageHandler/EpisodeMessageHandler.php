<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Episode;
use Labstag\Message\EpisodeMessage;
use Labstag\Repository\EpisodeRepository;
use Labstag\Service\EpisodeService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class EpisodeMessageHandler
{
    public function __construct(
        private EpisodeService $episodeService,
        private EpisodeRepository $episodeRepository,
    )
    {
    }

    public function __invoke(EpisodeMessage $episodeMessage): void
    {
        $episodeId = $episodeMessage->getEpisode();
        $episode   = $this->episodeRepository->find($episodeId);
        if (!$episode instanceof Episode) {
            return;
        }

        $this->episodeService->update($episode);
        $this->episodeRepository->save($episode);
    }
}
