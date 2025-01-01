<?php

namespace Labstag\FrontForm;

use Labstag\Form\Front\ContactType;
use Labstag\Lib\FrontFormLib;
use Override;
use Symfony\Component\Form\Form;

class ContactFrontForm extends FrontFormLib
{
    #[Override]
    public function execute(Form $form)
    {
        $state = parent::execute($form);
        if (!$state) {
            return;
        }

        $email = $this->emailService->get(
            'send_formcontact',
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

    public function getCode()
    {
        return 'contact';
    }

    public function getForm()
    {
        return ContactType::class;
    }

    public function getName(): string
    {
        return 'Formulaire contact';
    }
}
