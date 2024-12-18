<?php

namespace Labstag\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Locale;
use Labstag\Entity\User;
use Labstag\Repository\TemplateRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Workflow\Transition;

class UserService
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected TemplateService $templateService,
        protected TemplateRepository $templateRepository,
        protected SiteService $siteService,
        protected MailerInterface $mailer
    )
    {
    }

    public function actionWithTransition(Transition $transition, User $user)
    {
        $configuration = $this->siteService->getConfiguration();
        $email         = new Email();
        $data          = [
            'user'          => $user,
            'configuration' => $configuration,
        ];
        $templates = [
            'submit'         => 'user_submit',
            'approval'       => 'user_approval',
            'passwordlost'   => 'user_passwordlost',
            'changepassword' => 'user_changepassword',
            'deactivate'     => 'user_deactivate',
            'activate'       => 'user_activate',
        ];

        $name = $transition->getName();
        if (isset($templates[$name])) {
            $template = $this->templateService->get($templates[$name], $data);
            $email->to('submit' === $name ? $configuration->getEmail() : $user->getEmail());
        }

        $email->from($configuration->getNoReply());
        if (is_null($template)) {
            return;
        }

        $email->subject($template->getSubject());
        $email->text($template->getText());
        $email->html($template->getHtml());

        $this->mailer->send($email);
    }

    public function getLanguages(): array
    {
        return [
            'fr',
            'en',
        ];
    }

    public function getLanguagesForChoices(): array
    {
        $data      = $this->getLanguages();
        $languages = [];
        foreach ($data as $key) {
            $languages[$key] = Locale::new($key)->getAsDto()->getName();
        }

        return array_flip($languages);
    }

    public function getRoles(): array
    {
        return [
            'User'        => 'ROLE_USER',
            'Admin'       => 'ROLE_ADMIN',
            'Super Admin' => 'ROLE_SUPER_ADMIN',
        ];
    }

    public function hashPassword(User $user, string $password): string
    {
        return $this->userPasswordHasher->hashPassword($user, $password);
    }
}
