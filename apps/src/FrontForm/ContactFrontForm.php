<?php

namespace Labstag\FrontForm;

use Labstag\Form\Front\ContactType;
use Labstag\Lib\FrontFormLib;
use Override;
use Symfony\Component\Form\FormInterface;

class ContactFrontForm extends FrontFormLib
{
    #[Override]
    public function execute(FormInterface $form, bool $disable): bool
    {
        $state = parent::execute($form, $disable);
        if (!$state) {
            return false;
        }

        $email = $this->emailService->get(
            'send_contact',
            [
                'form' => $form->all(),
            ]
        );
        if (!$email instanceof \Labstag\Lib\EmailLib) {
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
