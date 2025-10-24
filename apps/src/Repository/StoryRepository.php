<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Story;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Story>
 */
class StoryRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Story::class);
    }

    public function findLastByNbr(int $nbr): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setMaxResults($nbr);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'stories-last-' . $nbr);

        return $query->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(s.id)');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'stories-total-enable');

        return $query->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $query        = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'stories-activate');

        return $query->getResult();
    }

    /**
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'stories-query-paginator');

        return $query;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->innerJoin('s.chapters', 'c');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->andWhere('c.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('s.createdAt', 'DESC');
    }
}
