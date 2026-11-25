<?php

namespace Labstag\Repository;

use DateTime;
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

    public function delete(object $entity): void
    {
        if ($entity instanceof Person) {
            $castings = $this->getEntityManager()->getRepository(Casting::class)->findBy(
                ['refPerson' => $entity]
            );
            foreach ($castings as $casting) {
                $this->getEntityManager()->remove($casting);
            }
        }

        parent::delete($entity);
    }

    /**
     * @return string[]
     */
    public function findStale(int $days = 30): array
    {
        $threshold = new DateTime(sprintf('-%d days', $days));

        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('p.id');
        $queryBuilder->andWhere('p.deathday IS NULL');
        $queryBuilder->andWhere('p.updatedAt IS NULL OR p.updatedAt < :threshold');
        $queryBuilder->setParameter('threshold', $threshold);

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getQueryBuilder(array $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');

        return $queryBuilder->orderBy('p.' . $query['order'], $query['orderby']);
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

    public function remove(object $entity): void
    {
        if ($entity instanceof Person) {
            $this->getEntityManager()->flush();
        }

        parent::remove($entity);
    }
}
