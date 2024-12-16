<?php

namespace Labstag\Event\Subscriber;

use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Entity\User;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Transition;

class WorkflowSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.chapter.transition' => 'onWorkflowTransition',
            'workflow.edito.transition'   => 'onWorkflowTransition',
            'workflow.story.transition'   => 'onWorkflowTransition',
            'workflow.memo.transition'    => 'onWorkflowTransition',
            'workflow.post.transition'    => 'onWorkflowTransition',
            'workflow.user.transition'    => 'onWorkflowTransition',
        ];
    }

    public function onWorkflowChapterTransition(Transition $transition, $entity): void
    {
        if (!$entity instanceof Chapter) {
            return;
        }

        unset($transition);
    }

    public function onWorkflowEditoTransition(Transition $transition, $entity): void
    {
        if (!$entity instanceof Edito) {
            return;
        }

        unset($transition);
    }

    public function onWorkflowMemoTransition(Transition $transition, $entity): void
    {
        if (!$entity instanceof Memo) {
            return;
        }

        unset($transition);
    }

    public function onWorkflowPostTransition(Transition $transition, $entity): void
    {
        if (!$entity instanceof Post) {
            return;
        }

        unset($transition);
    }

    public function onWorkflowStoryTransition(Transition $transition, $entity): void
    {
        if (!$entity instanceof Story) {
            return;
        }

        unset($transition);
    }

    public function onWorkflowTransition(Event $event): void
    {
        /** @var Transition $transition */
        $transition = $event->getTransition();
        $subject    = $event->getSubject();
        $this->onWorkflowChapterTransition($transition, $subject);
        $this->onWorkflowEditoTransition($transition, $subject);
        $this->onWorkflowMemoTransition($transition, $subject);
        $this->onWorkflowPostTransition($transition, $subject);
        $this->onWorkflowStoryTransition($transition, $subject);
        $this->onWorkflowUserTransition($transition, $subject);
    }

    public function onWorkflowUserTransition(Transition $transition, $entity): void
    {
        if (!$entity instanceof User) {
            return;
        }

        if ('submit' == $transition) {
            dump('aa');
        }

        if ('passwordlost' == $transition) {
            dump('bb');
        }
    }
}
