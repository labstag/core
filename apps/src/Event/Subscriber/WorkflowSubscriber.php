<?php

namespace Labstag\Event\Subscriber;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.chapter.transition' => 'onWorkflowChapterTransition',
            'workflow.edito.transition'   => 'onWorkflowEditoTransition',
            'workflow.story.transition'   => 'onWorkflowStoryTransition',
            'workflow.memo.transition'    => 'onWorkflowMemoTransition',
            'workflow.post.transition'    => 'onWorkflowPostTransition',
            'workflow.user.transition'    => 'onWorkflowUserTransition',
        ];
    }

    public function onWorkflowChapterTransition(Event $event): void
    {
        unset($event);
    }

    public function onWorkflowEditoTransition(Event $event): void
    {
        unset($event);
    }

    public function onWorkflowMemoTransition(Event $event): void
    {
        unset($event);
    }

    public function onWorkflowPostTransition(Event $event): void
    {
        unset($event);
    }

    public function onWorkflowStoryTransition(Event $event): void
    {
        unset($event);
    }

    public function onWorkflowUserTransition(Event $event): void
    {
        unset($event);
    }
}
