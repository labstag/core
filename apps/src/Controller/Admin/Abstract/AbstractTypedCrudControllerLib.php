<?php

namespace Labstag\Controller\Admin\Abstract;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

/**
 * Abstract controller for typed entities (Category, Tag) to reduce code duplication
 * across specific type controllers.
 */
abstract class AbstractTypedCrudControllerLib extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    /**
     * Create entity with proper type setting.
     */
    #[\Override]
    public function createEntity(string $entityFqcn): object
    {
        $entity = new $entityFqcn();
        $entity->setType($this->getEntityType());

        return $entity;
    }

    #[\Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $queryBuilder->andWhere('entity.type = :type');
        $queryBuilder->setParameter('type', $this->getEntityType());

        return $queryBuilder;
    }

    /**
     * Configure base fields with child count.
     */
    protected function configureBaseFields(string $pageName): array
    {
        $fields   = iterator_to_array(parent::configureFields($pageName));
        $fields[] = $this->crudFieldFactory->totalChildField($this->getChildRelationshipProperty());

        return $fields;
    }

    /**
     * Get the child relationship property name for counting.
     */
    abstract protected function getChildRelationshipProperty(): string;

    /**
     * Get the type identifier for this specific controller.
     */
    abstract protected function getEntityType(): string;
}
