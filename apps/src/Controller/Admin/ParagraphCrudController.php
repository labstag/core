<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphParentField;
use Labstag\Filter\DiscriminatorTypeFilter;
use Symfony\Component\Translation\TranslatableMessage;

class ParagraphCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        if ($this->isIframeEdit()) {
            $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

            return $actions;
        }

        $this->configureActionsTrash($actions);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(
            function ($paragraph, ?string $pageName): TranslatableMessage {
                unset($pageName);
                if (is_null($paragraph)) {
                    return new TranslatableMessage('Paragraph');
                }

                $name = $this->paragraphService->getName($paragraph);

                return new TranslatableMessage(
                    'Paragraph %name%',
                    ['%name%' => $name]
                );
            }
        );
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
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [ParagraphParentField::new('parent', new TranslatableMessage('Parent'))]
        );
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            $this->paragraphService->getFields($currentEntity, $pageName)
        );

        $this->crudFieldFactory->setTabDate($pageName);

        $this->crudFieldFactory->setTabConfig();

        $choiceField = ChoiceField::new('fond', new TranslatableMessage('Fond'))->hideOnIndex();
        $choiceField->setChoices($this->paragraphService->getFonds());

        $textField = TextField::new('id', new TranslatableMessage('Type'))->formatValue(
            function (?string $value) {
                $paragraph = $this->paragraphService->getParagraph($value);
                if (is_null($paragraph)) {
                    return $value;
                }

                return $paragraph->getName();
            }
        );
        $textField->setDisabled(true);

        $classesField = TextField::new('classes', new TranslatableMessage('classes'));
        $classesField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab('config', [$choiceField, $textField, $classesField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $types = $this->paragraphService->getAll(null);
        if ([] == $types) {
            return $filters;
        }

        $discriminatorTypeFilter = DiscriminatorTypeFilter::new('type', new TranslatableMessage('Type'));
        $discriminatorTypeFilter->setParagraphService($this->paragraphService);
        $discriminatorTypeFilter->setChoices(
            array_merge(
                ['' => ''],
                $types
            )
        );

        $filters->add($discriminatorTypeFilter);

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
