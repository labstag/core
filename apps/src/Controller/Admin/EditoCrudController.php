<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Edito;
use Labstag\Form\Paragraphs\EditoType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class EditoCrudController extends AbstractCrudControllerLib
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
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldImageUpload('img', $pageName);
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, EditoType::class),
            $this->addFieldRefUser()
        );
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(EntityFilter::new('refuser'));
        $filters->add(BooleanFilter::new('enable'));

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $edito = new $entityFqcn();
        $this->workflowService->init($edito);
        $edito->setRefuser($this->getUser());

        return $edito;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Edito::class;
    }
}
