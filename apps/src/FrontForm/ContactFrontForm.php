<?php

namespace Labstag\FrontForm;

use Labstag\Form\Front\ContactType;
use Labstag\Lib\EmailLib;
use Labstag\Lib\FrontFormLib;
use Override;
use Symfony\Component\Form\FormInterface;

class ContactFrontForm extends FrontFormLib
{
    #[Override]
    public function execute(bool $save, FormInterface $form, bool $disable): bool
    {
        $state = parent::execute($save, $form, $disable);
        if (!$state) {
            return false;
        }

        $email = $this->emailService->get(
            'send_contact',
            [
                'form' => $form->all(),
            ]
        );
        if (!$email instanceof EmailLib) {
            return false;
        }

        $email->init();
        $this->mailer->send($email);

        return true;
    }

    public function getCode(): string
    {
        return 'contact';
    }

    public function getForm(): string
    {
        return ContactType::class;
    }

    public function getName(): string
    {
        return 'Formulaire contact';
    }
}
