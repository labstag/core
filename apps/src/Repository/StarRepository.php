<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Star;

/**
 * @extends RepositoryAbstract<Star>
 */
class StarRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Star::class);
    }

    public function findAllData(string $type): mixed
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder = $queryBuilder->select('s.' . $type . ', count(s.id) as count');
        $queryBuilder->groupBy('s.' . $type);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'star-' . md5($type));

        return $query->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(s.id)');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'star-total-enable');

        return $query->getSingleScalarResult();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('s.title', 'ASC');
    }

    /**
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'star-query-paginator');

        return $query;
    }
}
