<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Message\StoryAllMessage;
use Labstag\Message\StoryMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class StoryActionService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function updateAll(): void
    {
        $this->messageBus->dispatch(new StoryAllMessage());
    }

    public function updateStory(int $storyId): void
    {
        $this->messageBus->dispatch(new StoryMessage($storyId));
    }
}
