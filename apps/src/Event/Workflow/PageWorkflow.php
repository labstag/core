<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class PageWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'page', transition: 'fix')]
    public function onFix(TransitionEvent $transitionEvent): void
    {
        $page = $transitionEvent->getSubject();
        unset($page);
        // Ton code pour la transition "correct"
    }

    #[AsTransitionListener(workflow: 'page', transition: 'publish')]
    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $page = $transitionEvent->getSubject();
        unset($page);
        // Ton code pour la transition "publish"
    }

    #[AsTransitionListener(workflow: 'page', transition: 'reject')]
    public function onReject(TransitionEvent $transitionEvent): void
    {
        $page = $transitionEvent->getSubject();
        unset($page);
        // Ton code pour la transition "reject"
    }

    #[AsTransitionListener(workflow: 'page', transition: 'reread')]
    public function onReread(TransitionEvent $transitionEvent): void
    {
        $page = $transitionEvent->getSubject();
        unset($page);
        // Ton code pour la transition "reread"
    }

    #[AsTransitionListener(workflow: 'page', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $page = $transitionEvent->getSubject();
        unset($page);
        // Ton code : ex. mettre à jour une date, notifier un user...
    }
}
