<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class EditoWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'edito', transition: 'fix')]
    public function onFix(TransitionEvent $transitionEvent): void
    {
        $edito = $transitionEvent->getSubject();
        unset($edito);
        // Ton code pour la transition "correct"
    }

    #[AsTransitionListener(workflow: 'edito', transition: 'publish')]
    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $edito = $transitionEvent->getSubject();
        unset($edito);
        // Ton code pour la transition "publish"
    }

    #[AsTransitionListener(workflow: 'edito', transition: 'reject')]
    public function onReject(TransitionEvent $transitionEvent): void
    {
        $edito = $transitionEvent->getSubject();
        unset($edito);
        // Ton code pour la transition "reject"
    }

    #[AsTransitionListener(workflow: 'edito', transition: 'reread')]
    public function onReread(TransitionEvent $transitionEvent): void
    {
        $edito = $transitionEvent->getSubject();
        unset($edito);
        // Ton code pour la transition "reread"
    }

    #[AsTransitionListener(workflow: 'edito', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $edito = $transitionEvent->getSubject();
        unset($edito);
        // Ton code : ex. mettre à jour une date, notifier un user...
    }
}
