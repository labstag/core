<?php

namespace Labstag\Event\Workflow;

use Labstag\Email\EmailAbstract;
use Labstag\Entity\User;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;

class UserWorkflow extends WorkflowAbstract
{
    #[AsTransitionListener(workflow: 'user', transition: 'activate')]
    public function onActivate(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->sendMail('user_activate', $user);
    }

    #[AsTransitionListener(workflow: 'user', transition: 'approval')]
    public function onApproval(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->sendMail('user_approval', $user);
    }

    #[AsTransitionListener(workflow: 'user', transition: 'changepassword')]
    public function onChangePassword(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->sendMail('user_changepassword', $user);
    }

    #[AsTransitionListener(workflow: 'user', transition: 'deactivate')]
    public function onDeactivate(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->sendMail('user_deactivate', $user);
    }

    #[AsTransitionListener(workflow: 'user', transition: 'passwordlost')]
    public function onPasswordLost(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->sendMail('user_passwordlost', $user);
    }

    #[AsTransitionListener(workflow: 'user', transition: 'submit')]
    public function onSubmit(TransitionEvent $transitionEvent): void
    {
        $user = $transitionEvent->getSubject();
        $this->sendMail('user_submit', $user);
    }

    protected function sendMail(string $template, User $user): void
    {
        $configuration = $this->configurationService->getConfiguration();
        $data          = [
            'user'          => $user,
            'configuration' => $configuration,
        ];

        $email = $this->emailService->get($template, $data);
        if (!$email instanceof EmailAbstract) {
            return;
        }

        $email->init();
        $email->to($user->getEmail());

        $this->emailService->send($email);
    }
}
