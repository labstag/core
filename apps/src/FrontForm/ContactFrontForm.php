<?php

namespace Labstag\FrontForm;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Form\Front\ContactType;
use Labstag\Lib\EmailLib;
use Labstag\Lib\FrontFormLib;
use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;

class ContactFrontForm extends FrontFormLib
{
    #[Override]
    public function execute(bool $save, FormInterface $form, bool $disable): bool
    {
        $state = parent::execute($save, $form, $disable);
        if (!$state) {
            return false;
        }

        $email = $this->emailService->get('send_contact', [
            'form' => $form->all(),
        ]);
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

    #[Override]
    public function getFields(array $data): iterable
    {
        yield TextField::new('firstname', new TranslatableMessage('first name'))->setValue($data['firstname']);
        yield TextField::new('lastname', new TranslatableMessage('last name'))->setValue($data['lastname']);
        yield TextareaField::new('content', new TranslatableMessage('content'))->setValue($data['content']);
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
