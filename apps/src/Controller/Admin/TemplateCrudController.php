<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Template;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TemplateCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        unset($pageName);
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        yield TextField::new('code', new TranslatableMessage('code'));
        $wysiwygField  = WysiwygField::new('html', new TranslatableMessage('html'))->onlyOnForms();
        $textareaField = TextareaField::new('text', new TranslatableMessage('text'))->onlyOnForms();

        if (!is_null($currentEntity)) {
            $template = $this->emailService->get($currentEntity->getCode());
            if (!is_null($template)) {
                $wysiwygField->setHelp($template->getHelp());
                $textareaField->setHelp($template->getHelp());
            }
        }

        yield $wysiwygField;
        yield $textareaField;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Template::class;
    }
}
