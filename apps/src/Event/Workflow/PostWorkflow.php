<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class PostWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'post', transition: 'fix')]
    public function onFix(TransitionEvent $transitionEvent): void
    {
        $post = $transitionEvent->getSubject();
        unset($post);
        // Ton code pour la transition "correct"
    }

    #[AsTransitionListener(workflow: 'post', transition: 'publish')]
    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $post = $transitionEvent->getSubject();
        unset($post);
        // Ton code pour la transition "publish"
    }

    #[AsTransitionListener(workflow: 'post', transition: 'reject')]
    public function onReject(TransitionEvent $transitionEvent): void
    {
        $post = $transitionEvent->getSubject();
        unset($post);
        // Ton code pour la transition "reject"
    }

    #[AsTransitionListener(workflow: 'post', transition: 'reread')]
    public function onReread(TransitionEvent $transitionEvent): void
    {
        $post = $transitionEvent->getSubject();
        unset($post);
        // Ton code pour la transition "reread"
    }

    #[AsTransitionListener(workflow: 'post', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $post = $transitionEvent->getSubject();
        unset($post);
        // Ton code : ex. mettre à jour une date, notifier un user...
    }
}
