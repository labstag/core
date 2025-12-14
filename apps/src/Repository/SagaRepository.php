<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Saga;

/**
 * @extends RepositoryAbstract<Saga>
 */
class SagaRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Saga::class);
    }

    /**
     * @return array<Saga>
     */
    public function findAllByTypeMovieEnable(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->orderBy('s.title', 'ASC');
        $queryBuilder->leftJoin('s.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.enable = true');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'saga-type-movie');

        return $query->getResult();
    }

    /**
     * @return array<Saga>
     */
    public function findSagaWithoutMovie(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.id IS NULL');
        $queryBuilder->orderBy('s.title', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('s.createdAt', 'DESC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'sagas-activate');

        return $query->getResult();
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getQueryBuilder(array $query): QueryBuilder
    {
        $subQuery = $this->createQueryBuilder('s2');
        $subQuery->select('s2.id');
        $subQuery->join('s2.movies', 'm2');
        $subQuery->where('m2.enable = :enable');
        $subQuery->setParameter('enable', true);
        $subQuery->groupBy('s2.id');
        $subQuery->having('COUNT(m2.id) >= 2');

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->andWhere($queryBuilder->expr()->in('s.id', $subQuery->getDQL()));

        return $queryBuilder->orderBy('s.' . $query['order'], $query['orderby']);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(array $query): Query
    {
        $queryBuilder = $this->getQueryBuilder($query);
        $query        = $queryBuilder->getQuery();
        $dql          = $query->getDQL();
        $query->enableResultCache(3600, 'sagas-query-paginator-' . md5((string) $dql));

        return $query;
    }

    /**
     * @return array<Saga>
     */
    public function showPublic(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->orderBy('s.title', 'ASC');
        $queryBuilder->andWhere('s.enable = true');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'saga-public');

        return $query->getResult();
    }
}
