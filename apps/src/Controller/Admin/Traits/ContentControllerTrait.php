<?php

namespace Labstag\Controller\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Trait for content controllers (Post, Page, Story, etc.) with common actions and filters.
 */
trait ContentControllerTrait
{
    protected function configureCommonContentActions(Actions $actions, string $w3cRoute, string $publicRoute): Actions
    {
        // Base actions (trash + navigation + detail)
        $this->configureActionsTrash($actions);
        $this->setEditDetail($actions);

        // Public and W3C actions via factory (inherited from abstract)
        $this->setActionPublic($actions, $w3cRoute, $publicRoute);

        return $actions;
    }

    protected function configureCommonContentCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    protected function configureCommonContentFields(string $contentType, string $pageName, bool $isSuperAdmin): iterable
    {
        // Principal tab + full content set (identity + taxonomy + paragraphs + meta + ref user)
        yield $this->addTabPrincipal();
        foreach ($this->crudFieldFactory->fullContentSet(
            $contentType,
            $pageName,
            static::getEntityFqcn(),
            $isSuperAdmin
        ) as $field) {
            yield $field;
        }
    }

    protected function configureCommonContentFilters(Filters $filters, string $contentType): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);
        $this->crudFieldFactory->addFilterTags($filters, $contentType);
        $this->crudFieldFactory->addFilterCategories($filters, $contentType);

        return $filters;
    }

    protected function configureCommonWorkflowAndDates(): iterable
    {
        // Workflow + states
        yield $this->crudFieldFactory->workflowField();
        yield $this->crudFieldFactory->stateField();
        // Dates
        foreach ($this->crudFieldFactory->dateSet() as $field) {
            yield $field;
        }
    }

    protected function createCommonPublicLink(string $entity, string $routeName): RedirectResponse
    {
        unset($routeName);
        $serviceEntityRepositoryLib = $this->getRepository();
        $content                    = $serviceEntityRepositoryLib->find($entity);

        return $this->publicLink($content);
    }

    protected function createCommonW3cLink(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $content                    = $serviceEntityRepositoryLib->find($entity);

        return $this->linkw3CValidator($content);
    }
}
