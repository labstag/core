<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Story;
use Labstag\Lib\ServiceEntityRepositoryLib;

class StoryRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Story::class);
    }

    public function findLastByNbr(int $nbr)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setMaxResults($nbr);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findTotalEnable()
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(h.id)');

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getAllActivate()
    {
        $queryBuilder = $this->getQueryBuilder();
        $query        = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getQueryPaginator()
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }

    private function getQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('h');
        $queryBuilder->innerJoin('h.chapters', 'c');
        $queryBuilder->where('h.enable = :enable');
        $queryBuilder->andWhere('c.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('h.createdAt', 'DESC');
    }
}
