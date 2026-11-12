<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Casting;
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
     * @param array<string, mixed> $query
     */
    public function getQueryBuilder(array $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->distinct();
        $queryBuilder->innerJoin('p.castings', 'c');
        $queryBuilder->leftJoin('c.refMovie', 'm', 'WITH', 'm.enable = :enable');
        $queryBuilder->leftJoin('c.refSerie', 's', 'WITH', 's.enable = :enable');
        $queryBuilder->leftJoin('c.refSeason', 'sea', 'WITH', 'sea.enable = :enable');
        $queryBuilder->leftJoin('sea.refserie', 's2', 'WITH', 's2.enable = :enable');
        $queryBuilder->leftJoin('c.refEpisode', 'e', 'WITH', 'e.enable = :enable');
        $queryBuilder->leftJoin('e.refseason', 'sea2', 'WITH', 'sea2.enable = :enable');
        $queryBuilder->leftJoin('sea2.refserie', 's3', 'WITH', 's3.enable = :enable');
        $queryBuilder->where('m.id IS NOT NULL');
        $queryBuilder->orWhere('s.id IS NOT NULL');
        $queryBuilder->orWhere('sea.id IS NOT NULL AND s2.id IS NOT NULL');
        $queryBuilder->orWhere('e.id IS NOT NULL AND sea2.id IS NOT NULL AND s3.id IS NOT NULL');
        $queryBuilder->setParameter('enable', true);

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
            $castings = $this->getEntityManager()->getRepository(Casting::class)->findBy(
                ['refPerson' => $entity]
            );
            foreach ($castings as $casting) {
                $this->getEntityManager()->remove($casting);
            }

            $this->getEntityManager()->flush();
        }

        parent::remove($entity);
    }
}
