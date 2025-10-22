<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphParentField;
use Symfony\Component\Translation\TranslatableMessage;

class ParagraphCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        if ($this->isIframeEdit()) {
            $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

            return $actions;
        }

        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Paragraph'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Paragraphs'));
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

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->crudFieldFactory->idField();
        yield ParagraphParentField::new('parent', new TranslatableMessage('Parent'));
        foreach ($this->paragraphService->getFields($currentEntity, $pageName) as $field) {
            yield $field;
        }

        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }

        yield FormField::addTab(new TranslatableMessage('Config'));
        $choiceField = ChoiceField::new('fond', new TranslatableMessage('Fond'))->hideOnIndex();
        $choiceField->setChoices($this->paragraphService->getFonds());
        yield $choiceField;
        $allTypes  = array_flip($this->paragraphService->getAll(null));
        $textField = TextField::new('type', new TranslatableMessage('Type'))->formatValue(
            static fn ($value) => $allTypes[$value] ?? null
        );
        $textField->setDisabled(true);
        yield $textField;
        yield TextField::new('classes', new TranslatableMessage('classes'))->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $types = $this->paragraphService->getAll(null);
        if ([] !== $types) {
            $filters->add(ChoiceFilter::new('type', new TranslatableMessage('Type'))->setChoices($types));
        }

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Paragraph::class;
    }

    private function isIframeEdit(): bool
    {
        $query = $this->requestStack->getCurrentRequest()->query->all();

        return isset($query['iframe']);
    }
}
