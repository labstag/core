<?php

namespace Labstag\FrontForm;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Email\Abstract\EmailLib;
use Labstag\Form\Front\ContactType;
use Labstag\FrontForm\Abstract\FrontFormLib;
use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;

class ContactFrontForm extends FrontFormLib
{
    /**
     * @param FormInterface<mixed> $form
     */
    #[Override]
    public function execute(FormInterface $form, bool $disable = false, bool $save = true): bool
    {
        $state = parent::execute($form, $disable, $save);
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

    /**
     * @param array<string, mixed> $data
     *
     * @return iterable<mixed>
     */
    #[Override]
    public function getFields(array $data): iterable
    {
        yield TextField::new('firstname', new TranslatableMessage('First name'))->setValue($data['firstname']);
        yield TextField::new('lastname', new TranslatableMessage('Last name'))->setValue($data['lastname']);
        yield TextareaField::new('content', new TranslatableMessage('Content'))->setValue($data['content']);
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
