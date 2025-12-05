<?php

namespace Labstag\FrontForm;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Email\EmailAbstract;
use Labstag\Form\Front\ContactType;
use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;

class ContactFrontForm extends FrontFormAbstract
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
        if (!$email instanceof EmailAbstract) {
            return false;
        }

        $email->init();
        $this->emailService->send($email);

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
    public function getFields(array $data): array
    {
        $textField = TextField::new('firstname', new TranslatableMessage('First name'));
        $textField->setValue($data['firstname']);

        $lastName = TextField::new('lastname', new TranslatableMessage('Last name'));
        $lastName->setValue($data['lastname']);

        $textareaField = TextareaField::new('content', new TranslatableMessage('Content'));
        $textareaField->setValue($data['content']);

        return [
            $textField,
            $lastName,
            $textareaField,
        ];
    }

    public function getForm(): string
    {
        return ContactType::class;
    }

    public function getName(): string
    {
        return new TranslatableMessage('Form contact');
    }
}
