<?php

namespace Labstag\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;

/**
 * @extends ServiceEntityRepositoryAbstract<Post>
 */
class PostRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Post::class);
    }

    public function findLastByNbr(int $nbr): mixed
    {
        $queryBuilder = $this->getOptimizedBaseQB();
        $queryBuilder->setMaxResults($nbr);

        return $this->cacheQuery($queryBuilder->getQuery(), 'last-' . $nbr)->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('count(p.id)');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->andWhere('p.createdAt <= :now');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->setParameter('now', new DateTime('now'));

        return $this->cacheQuery($queryBuilder->getQuery(), 'total-enable', 900)->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getOptimizedBaseQB();

        return $this->cacheQuery($queryBuilder->getQuery(), 'activate', 600)->getResult();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->andWhere('p.createdAt <= :now');
        $queryBuilder->setParameter('now', new DateTime('now'));

        return $queryBuilder->orderBy('p.createdAt', 'DESC');
    }

    /**
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getOptimizedBaseQB();

        return $this->cacheQuery($queryBuilder->getQuery(), 'query-paginator', 300);
    }

    /**
     * @param Query<mixed, mixed> $query
     *
     * @return Query<mixed, mixed>
     */
    private function cacheQuery(Query $query, string $suffix, int $ttl = 600): Query
    {
        // TTL réduit pour contenu récent ; ajustable selon stratégie
        $query->enableResultCache($ttl, 'post-' . $suffix);

        return $query;
    }

    /**
     * Base optimisée : pré-chargement des relations nécessaires pour éviter N+1.
     */
    private function getOptimizedBaseQB(): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder();
        // Relations hypothétiques : tags, categories, meta (ajuster selon mapping réel)
        $queryBuilder->leftJoin('p.tags', 't')->addSelect('t');
        if ($this->getEntityManager()->getClassMetadata(Post::class)->hasAssociation('categories')) {
            $queryBuilder->leftJoin('p.categories', 'c')->addSelect('c');
        }

        if ($this->getEntityManager()->getClassMetadata(Post::class)->hasAssociation('meta')) {
            $queryBuilder->leftJoin('p.meta', 'm')->addSelect('m');
        }

        return $queryBuilder;
    }
}
