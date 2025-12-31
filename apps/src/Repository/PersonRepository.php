<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Person;

/**
 * @extends RepositoryAbstract<Person>
 */
class PersonRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
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
     * @param array<string, mixed> $query
     */
    public function getQueryBuilder(array $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('p.' . $query['order'], $query['orderby']);
    }
}
