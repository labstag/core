<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphParentField;
use Labstag\Lib\AbstractCrudControllerLib;
use Symfony\Component\Translation\TranslatableMessage;
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

        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addFieldID();
        $choiceField = ChoiceField::new('fond', new TranslatableMessage('Fond'));
        $choiceField->setChoices($this->paragraphService->getFonds());
        yield $choiceField;
        $allTypes = array_flip($this->paragraphService->getAll(null));
        $textField = TextField::new('type', new TranslatableMessage('Type'))->formatValue(
            static fn ($value) => $allTypes[$value] ?? null
        );
        $textField->setDisabled(true);

        yield $textField;
        yield ParagraphParentField::new('parent', new TranslatableMessage('Parent'));
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        $fields = $this->paragraphService->getFields($currentEntity, $pageName);
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(
            ChoiceFilter::new('type', new TranslatableMessage('Type'))->setChoices(
                $this->paragraphService->getAll(
                    null
                )
            )
        );

        return $filters;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Paragraph::class;
    }

    private function isIframeEdit(): bool
    {
        $query = $this->requestStack->getCurrentRequest()->query->all();

        return isset($query['iframe']) && isset($query['crudAction']) && $query['crudAction'] === 'edit';
    }
}
