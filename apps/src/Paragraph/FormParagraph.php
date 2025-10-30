<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Labstag\FrontForm\FrontFormAbstract;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class FormParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        $formCode = $paragraph->getForm();
        $save     = $paragraph->isSave();
        if (is_null($formCode)) {
            $this->setShow($paragraph, false);

            return;
        }

        $formClass = $this->formService->get($formCode);
        if (!$formClass instanceof FrontFormAbstract) {
            $this->setShow($paragraph, false);

            return;
        }

        $form = $this->createForm($formClass->getForm());

        $execute = $this->formService->execute($form, $formCode, $disable, $save);
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

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        $choiceField = ChoiceField::new('form', new TranslatableMessage('Form'));
        $choiceField->hideOnIndex();
        $choiceField->setChoices($this->formService->all());
        yield $choiceField;
        yield BooleanField::new('save', new TranslatableMessage('Save data in database'));
        $wysiwygField = WysiwygField::new('content', new TranslatableMessage('Confirm message'));
        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Formulaire';
    }

    #[Override]
    public function getType(): string
    {
        return 'form';
    }

    #[\Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return $object::class == Page::class;
    }

    #[Override]
    public function templates(Paragraph $paragraph, string $type): array
    {
        $templates = $this->getTemplateContent($type, $this->getType() . '/' . $paragraph->getForm());

        if ($templates['view'] != end($templates['files'])) {
            return $templates;
        }

        return $this->getTemplateContent($type, $this->getType());
    }
}
