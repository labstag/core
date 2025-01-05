<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class FormParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable)
    {
        $formCode = $paragraph->getForm();
        if (is_null($formCode)) {
            $this->setShow($paragraph, false);

            return;
        }

        $formClass = $this->formService->get($formCode);
        if (is_null($formClass)) {
            $this->setShow($paragraph, false);

            return;
        }

        $form = $this->createForm(
            $formClass->getForm()
        );

        $execute = $this->formService->execute($formCode, $form, $disable);
        $this->setData(
            $paragraph,
            [
                'execute'   => $execute,
                'form'      => $form,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);
        $choiceField = ChoiceField::new('form', new TranslatableMessage('Formulaire'));
        $choiceField->hideOnIndex();
        $choiceField->setChoices($this->formService->all());
        yield $choiceField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Formulaire';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'form';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
