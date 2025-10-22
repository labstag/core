<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Edito;
use Symfony\Component\Translation\TranslatableMessage;

class EditoCrudController extends AbstractCrudControllerLib
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
        $crud->setEntityLabelInSingular(new TranslatableMessage('Edito'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Editos'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        $isSuperAdmin = $this->isSuperAdmin();
        // Edito n'a pas de slug : withSlug: false
        foreach ($this->crudFieldFactory->baseIdentitySet(
            $pageName,
            self::getEntityFqcn(),
            withSlug: false
        ) as $field) {
            yield $field;
        }

        foreach ($this->crudFieldFactory->paragraphFields($pageName) as $field) {
            yield $field;
        }

        foreach ($this->crudFieldFactory->refUserFields($isSuperAdmin) as $field) {
            yield $field;
        }

        yield $this->crudFieldFactory->workflowField();
        yield $this->crudFieldFactory->stateField();
        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Edito
    {
        $edito = new $entityFqcn();
        $this->workflowService->init($edito);
        $edito->setRefuser($this->getUser());

        return $edito;
    }

    public static function getEntityFqcn(): string
    {
        return Edito::class;
    }
}
