<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Page;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PageRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Page::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin('p.categories', 'categories')->addSelect('categories');
        $queryBuilder->leftJoin('p.children', 'children')->addSelect('children');
        $queryBuilder->leftJoin('p.page', 'page')->addSelect('page');
        $queryBuilder->leftJoin('p.paragraphs', 'paragraphs')->addSelect('paragraphs');
        $queryBuilder->leftJoin('p.refuser', 'refuser')->addSelect('refuser');
        $queryBuilder->leftJoin('p.tags', 'tags')->addSelect('tags');

        return $queryBuilder;

    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('p.createdAt', 'DESC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
