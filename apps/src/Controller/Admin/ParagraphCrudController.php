<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphParentField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class ParagraphCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        if ($this->isIframeEdit()) {
            $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

            return $actions;
        }

        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        if ($this->isIframeEdit()) {
            $crud->renderSidebarMinimized();
            $crud->overrideTemplates(
                ['layout' => 'admin/paragraph/layout.html.twig']
            );
        }

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addFieldID();
        $choiceField = ChoiceField::new('fond', 'Fond');
        $choiceField->setChoices($this->paragraphService->getFonds());
        yield $choiceField;
        $allTypes  = array_flip($this->paragraphService->getAll(null));
        $textField = TextField::new('type')->formatValue(
            static fn ($value) => $allTypes[$value] ?? null
        );
        $textField->setDisabled(true);

        yield $textField;
        yield ParagraphParentField::new('parent', 'Parent');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        $fields = $this->paragraphService->getFields($currentEntity, $pageName);
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Paragraph::class;
    }

    private function isIframeEdit()
    {
        $query = $this->requestStack->getCurrentRequest()->query->all();

        return isset($query['iframe']) && isset($query['crudAction']) && 'edit' === $query['crudAction'];
    }
}
