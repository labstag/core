<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Labstag\Entity\Tag;

class ChapterTagCrudController extends TagCrudController
{
    public function configureFields(string $pageName): iterable
    {
    $data   = parent::configureFields($pageName);
    $data[] = $this->crudFieldFactory->totalChildField('chapters');

        return $data;
    }

    public function createEntity(string $entityFqcn): Tag
    {
        $tag = new $entityFqcn();
        $tag->setType('chapter');

        return $tag;
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
        $queryBuilder->setParameter('type', 'chapter');

        return $queryBuilder;
    }
}
