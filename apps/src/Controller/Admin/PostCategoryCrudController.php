<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Labstag\Entity\Category;

class PostCategoryCrudController extends CategoryCrudController
{
    // Pas d'image : Category non uploadable
    public function configureFields(string $pageName): iterable
    {
    $data   = parent::configureFields($pageName);
    $data[] = $this->crudFieldFactory->totalChildField('posts');

        return $data;
    }

    public function createEntity(string $entityFqcn): Category
    {
        $category = new $entityFqcn();
        $category->setType('post');

        return $category;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $queryBuilder->andWhere('entity.type = :type');
        $queryBuilder->setParameter('type', 'post');

        return $queryBuilder;
    }
}
