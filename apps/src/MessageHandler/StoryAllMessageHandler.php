<?php

namespace Labstag\MessageHandler;

use Labstag\Message\StoryAllMessage;
use Labstag\Message\StoryMessage;
use Labstag\Repository\StoryRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class StoryAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageBus,
        private StoryRepository $storyRepository,
    )
    {
    }

    public function __invoke(StoryAllMessage $storyAllMessage): void
    {
        unset($storyAllMessage);
        $stories                          = $this->storyRepository->findAll();
        foreach ($stories as $story) {
            $this->messageBus->dispatch(new StoryMessage($story->getId()));
        }
    }
}
