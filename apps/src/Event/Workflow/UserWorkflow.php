<?php

namespace Labstag\Event\Workflow;

use Labstag\Message\UserEmailMessage;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class UserWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'user', transition: 'activate')]
    public function onActivate(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->messageBus->dispatch(new UserEmailMessage($user->getUsername(), 'user_activate'));
        $this->sendMail('user_activate', $user);
    }

    #[AsTransitionListener(workflow: 'user', transition: 'approval')]
    public function onApproval(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->messageBus->dispatch(new UserEmailMessage($user->getUsername(), 'user_approval'));
    }

    #[AsTransitionListener(workflow: 'user', transition: 'changepassword')]
    public function onChangePassword(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->messageBus->dispatch(new UserEmailMessage($user->getUsername(), 'user_changepassword'));
    }

    #[AsTransitionListener(workflow: 'user', transition: 'deactivate')]
    public function onDeactivate(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->messageBus->dispatch(new UserEmailMessage($user->getUsername(), 'user_deactivate'));
    }

    #[AsTransitionListener(workflow: 'user', transition: 'passwordlost')]
    public function onPasswordLost(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->messageBus->dispatch(new UserEmailMessage($user->getUsername(), 'user_passwordlost'));
    }

    #[AsTransitionListener(workflow: 'user', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->messageBus->dispatch(new UserEmailMessage($user->getUsername(), 'user_submit'));
    }
}
