<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Email\EmailAbstract;
use Labstag\Entity\Template;
use Labstag\Field\WysiwygField;
use Symfony\Component\Translation\TranslatableMessage;

class TemplateCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );
        $crud->setEntityLabelInSingular(new TranslatableMessage('Template'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Templates'));

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        $this->crudFieldFactory->setTabPrincipal();
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $textField = TextField::new('code', new TranslatableMessage('Code'));
        $textField->setDisabled(true);

        $wysiwygField  = WysiwygField::new('html', new TranslatableMessage('HTML'));
        $wysiwygField->onlyOnForms();

        $textareaField = TextareaField::new('text', new TranslatableMessage('Texte brut'));
        $textareaField->onlyOnForms();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->idField(),
                $textField,
                $this->crudFieldFactory->titleField(),
            ]
        );

        if (!is_null($currentEntity)) {
            $template = $this->emailService->get($currentEntity->getCode());
            if ($template instanceof EmailAbstract) {
                $wysiwygField->setHelp($template->getHelp());
                $textareaField->setHelp($template->getHelp());
            }
        }

        $this->crudFieldFactory->addFieldsToTab('principal', [$wysiwygField, $textareaField]);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    public static function getEntityFqcn(): string
    {
        return Template::class;
    }
}
