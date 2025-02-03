<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Labstag\Entity\Category;
use Override;

class MovieCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $data   = parent::configureFields($pageName);
        $data[] = $this->addFieldTotalChild('movies');

        return $data;
    }

    #[Override]
    public function createEntity(string $entityFqcn): Category
    {
        $category = new $entityFqcn();
        $category->setType('movie');

        return $category;
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $queryBuilder->andWhere('entity.type = :type');
        $queryBuilder->setParameter('type', 'movie');

        return $queryBuilder;
    }
}
