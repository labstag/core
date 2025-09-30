<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Edito;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Repository\ParagraphRepository;
use Labstag\Service\ParagraphService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class EditoCrudController extends AbstractCrudControllerLib
{
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        $isSuperAdmin = $this->isSuperAdmin();
    // Edito n'a pas de slug : withSlug: false
    foreach ($this->crudFieldFactory->baseIdentitySet('edito', $pageName, self::getEntityFqcn(), withSlug: false) as $field) { yield $field; }
        foreach ($this->crudFieldFactory->paragraphFields($pageName) as $field) { yield $field; }
        foreach ($this->crudFieldFactory->refUserFields($isSuperAdmin) as $field) { yield $field; }
        yield $this->crudFieldFactory->workflowField();
        yield $this->crudFieldFactory->stateField();
        foreach ($this->crudFieldFactory->dateSet() as $field) { yield $field; }
    }

    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

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
