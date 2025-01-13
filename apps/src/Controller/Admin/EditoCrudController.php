<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
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
    public function configureCrud(Crud $crud): Crud
    {
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldBoolean();
        yield $this->addFieldTitle();
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        yield $this->addFieldImageUpload('img', $pageName);
        $fields = array_merge($this->addFieldParagraphs($pageName, EditoType::class), $this->addFieldRefUser());
        foreach ($fields as $field) {
            yield $field;
        }

        yield $this->addFieldWorkflow();
        yield $this->addFieldState();
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterRefUser($filters);
        $this->addFilterEnable($filters);

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn): Edito
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
