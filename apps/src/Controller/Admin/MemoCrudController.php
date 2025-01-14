<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Entity\Memo;
use Labstag\Form\Paragraphs\MemoType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MemoCrudController extends AbstractCrudControllerLib
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
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        yield $this->addFieldTitle();
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        yield $this->addFieldImageUpload('img', $pageName);
        $fields = array_merge($this->addFieldParagraphs($pageName, MemoType::class), $this->addFieldRefUser());
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
    public function createEntity(string $entityFqcn): Memo
    {
        $memo = new $entityFqcn();
        $this->workflowService->init($memo);
        $memo->setRefuser($this->getUser());

        return $memo;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Memo::class;
    }
}
