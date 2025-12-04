<?php

namespace Labstag\MessageHandler;

use Labstag\Email\EmailAbstract;
use Labstag\Entity\User;
use Labstag\Message\UserEmailMessage;
use Labstag\Repository\UserRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\EmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserEmailMessageHandler
{
    public function __construct(
        private EmailService $emailService,
        private UserRepository $userRepository,
        private ConfigurationService $configurationService,
    )
    {
    }

    public function __invoke(UserEmailMessage $userEmailMessage): void
    {
        $username = $userEmailMessage->getUsername();
        $template = $userEmailMessage->getTemplate();

        $user = $this->userRepository->findOneBy([
                'username' => $username,
            ]);
        if (!$user instanceof User) {
            return;
        }

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
