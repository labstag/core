<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class StoryWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'story', transition: 'fix')]
    public function onFix(TransitionEvent $transitionEvent): void
    {
        $story = $transitionEvent->getSubject();
        unset($story);
        // Ton code pour la transition "correct"
    }

    #[AsTransitionListener(workflow: 'story', transition: 'publish')]
    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $story = $transitionEvent->getSubject();
        unset($story);
        // Ton code pour la transition "publish"
    }

    #[AsTransitionListener(workflow: 'story', transition: 'reject')]
    public function onReject(TransitionEvent $transitionEvent): void
    {
        $story = $transitionEvent->getSubject();
        unset($story);
        // Ton code pour la transition "reject"
    }

    #[AsTransitionListener(workflow: 'story', transition: 'reread')]
    public function onReread(TransitionEvent $transitionEvent): void
    {
        $story = $transitionEvent->getSubject();
        unset($story);
        // Ton code pour la transition "reread"
    }

    #[AsTransitionListener(workflow: 'story', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $story = $transitionEvent->getSubject();
        unset($story);
        // Ton code : ex. mettre à jour une date, notifier un user...
    }
}
