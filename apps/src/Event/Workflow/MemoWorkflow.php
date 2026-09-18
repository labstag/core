<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class MemoWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'memo', transition: 'fix')]
    public function onFix(TransitionEvent $transitionEvent): void
    {
        $memo = $transitionEvent->getSubject();
        unset($memo);
        // Ton code pour la transition "correct"
    }

    #[AsTransitionListener(workflow: 'memo', transition: 'publish')]
    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $memo = $transitionEvent->getSubject();
        unset($memo);
        // Ton code pour la transition "publish"
    }

    #[AsTransitionListener(workflow: 'memo', transition: 'reject')]
    public function onReject(TransitionEvent $transitionEvent): void
    {
        $memo = $transitionEvent->getSubject();
        unset($memo);
        // Ton code pour la transition "reject"
    }

    #[AsTransitionListener(workflow: 'memo', transition: 'reread')]
    public function onReread(TransitionEvent $transitionEvent): void
    {
        $memo = $transitionEvent->getSubject();
        unset($memo);
        // Ton code pour la transition "reread"
    }

    #[AsTransitionListener(workflow: 'memo', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $memo = $transitionEvent->getSubject();
        unset($memo);
        // Ton code : ex. mettre à jour une date, notifier un user...
    }
}
