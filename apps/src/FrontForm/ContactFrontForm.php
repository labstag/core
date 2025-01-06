<?php

namespace Labstag\FrontForm;

use Labstag\Form\Front\ContactType;
use Labstag\Lib\FrontFormLib;
use Override;
use Symfony\Component\Form\Form;

class ContactFrontForm extends FrontFormLib
{
    #[Override]
    public function execute(Form $form, bool $disable): void
    {
        $state = parent::execute($form, $disable);
        if (!$state) {
            return;
        }

        $email = $this->emailService->get(
            'send_contact',
            [
                'form' => $form->all(),
            ]
        );
        if (is_null($email)) {
            return;
        }

        $email->init();
        $this->mailer->send($email);
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
