<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Story;
use Labstag\Lib\ServiceEntityRepositoryLib;

class StoryRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Story::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.categories', 'categories')->addSelect('categories');
        $queryBuilder->leftJoin('s.chapters', 'chapters')->addSelect('chapters');
        $queryBuilder->leftJoin('s.paragraphs', 'paragraphs')->addSelect('paragraphs');
        $queryBuilder->leftJoin('s.refuser', 'refuser')->addSelect('refuser');
        $queryBuilder->leftJoin('s.tags', 'tags')->addSelect('tags');

        return $queryBuilder;

    }

    public function findLastByNbr(int $nbr): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setMaxResults($nbr);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(s.id)');

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $query        = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->andWhere('chapters.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('s.createdAt', 'DESC');
    }
}
