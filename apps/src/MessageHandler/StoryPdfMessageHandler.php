<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Story;
use Labstag\Message\StoryPdfMessage;
use Labstag\Repository\StoryRepository;
use Labstag\Service\StoryService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class StoryPdfMessageHandler
{
    public function __construct(
        private StoryService $storyService,
        private StoryRepository $storyRepository,
    )
    {
    }

    public function __invoke(StoryPdfMessage $storyPdfMessage): void
    {
        $storyId = $storyPdfMessage->getData();

        $story = $this->storyRepository->find($storyId);
        if (!$story instanceof Story) {
            return;
        }

        $this->storyService->setPdf($story);
        // do something with your message
    }
}
