<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class ChapterWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'chapter', transition: 'correct')]
    public function onCorrect(TransitionEvent $transitionEvent): void
    {
        $chapter = $transitionEvent->getSubject();
        unset($chapter);
        // Ton code pour la transition "correct"
    }

    #[AsTransitionListener(workflow: 'chapter', transition: 'publish')]
    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $chapter = $transitionEvent->getSubject();
        unset($chapter);
        // Ton code pour la transition "publish"
    }

    #[AsTransitionListener(workflow: 'chapter', transition: 'reject')]
    public function onReject(TransitionEvent $transitionEvent): void
    {
        $chapter = $transitionEvent->getSubject();
        unset($chapter);
        // Ton code pour la transition "reject"
    }

    #[AsTransitionListener(workflow: 'chapter', transition: 'reread')]
    public function onReread(TransitionEvent $transitionEvent): void
    {
        $chapter = $transitionEvent->getSubject();
        unset($chapter);
        // Ton code pour la transition "reread"
    }

    #[AsTransitionListener(workflow: 'chapter', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $chapter = $transitionEvent->getSubject();
        unset($chapter);
        // Ton code : ex. mettre à jour une date, notifier un user...
    }
}
