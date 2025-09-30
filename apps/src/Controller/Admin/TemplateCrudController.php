<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Template;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Lib\EmailLib;
use Symfony\Component\Translation\TranslatableMessage;

class TemplateCrudController extends AbstractCrudControllerLib
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
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        // Template n'a ni slug ni enable ni image : withSlug: false, withImage: false, withEnable: false
        foreach ($this->crudFieldFactory->baseIdentitySet(
            'template',
            $pageName,
            self::getEntityFqcn(),
            withSlug: false,
            withImage: false,
            withEnable: false
        ) as $field) {
            yield $field;
        }

        $textField = TextField::new('code', new TranslatableMessage('Code'));
        $textField->setDisabled(true);

        yield $textField;
        $wysiwygField  = WysiwygField::new('html', new TranslatableMessage('HTML'))->onlyOnForms();
        $textareaField = TextareaField::new('text', new TranslatableMessage('Texte brut'))->onlyOnForms();

        if (!is_null($currentEntity)) {
            $template = $this->emailService->get($currentEntity->getCode());
            if ($template instanceof EmailLib) {
                $wysiwygField->setHelp($template->getHelp());
                $textareaField->setHelp($template->getHelp());
            }
        }

        yield $wysiwygField;
        yield $textareaField;
    }

    public static function getEntityFqcn(): string
    {
        return Template::class;
    }
}
