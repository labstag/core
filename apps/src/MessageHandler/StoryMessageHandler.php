<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Story;
use Labstag\Message\StoryMessage;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\StoryRepository;
use Labstag\Service\StoryService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class StoryMessageHandler
{
    public function __construct(
        private StoryService $storyService,
        private MessageBusInterface $messageBus,
        private StoryRepository $storyRepository,
        private ChapterRepository $chapterRepository,
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

        // $this->storyService->setPdf($story);

        $this->storyRepository->save($story);
    }
}
