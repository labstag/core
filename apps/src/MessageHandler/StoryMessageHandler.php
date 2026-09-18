<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Story;
use Labstag\Message\StoryMessage;
use Labstag\Repository\StoryRepository;
use Labstag\Service\StoryService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class StoryMessageHandler
{
    public function __construct(
        private StoryService $storyService,
        private StoryRepository $storyRepository,
    )
    {
    }

    public function __invoke(StoryMessage $storyMessage): void
    {
        $storyId = $storyMessage->getData();
        $story   = $this->storyRepository->find($storyId);
        if (!$story instanceof Story) {
            return;
        }

        $this->storyService->update($story);

        $this->storyRepository->save($story);
    }
}
