<?php

namespace Labstag\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;
use Labstag\Lib\ServiceEntityRepositoryLib;
use Doctrine\ORM\AbstractQuery;

class PostRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Post::class);
    }

    private function cacheQuery(Query $query, string $suffix, int $ttl = 600): Query
    {
        // TTL réduit pour contenu récent ; ajustable selon stratégie
        $query->enableResultCache($ttl, 'post-' . $suffix);

        return $query;
    }

    public function findLastByNbr(int $nbr): mixed
    {
        $qb = $this->getOptimizedBaseQB();
        $qb->setMaxResults($nbr);

        return $this->cacheQuery($qb->getQuery(), 'last-' . $nbr)->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(p.id)');
        $qb->where('p.enable = :enable');
        $qb->andWhere('p.createdAt <= :now');
        $qb->setParameter('enable', true);
        $qb->setParameter('now', new DateTime('now'));

        return $this->cacheQuery($qb->getQuery(), 'total-enable', 900)->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $qb = $this->getOptimizedBaseQB();

        return $this->cacheQuery($qb->getQuery(), 'activate', 600)->getResult();
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

    public function getQueryPaginator(): Query
    {
        $qb = $this->getOptimizedBaseQB();

        return $this->cacheQuery($qb->getQuery(), 'query-paginator', 300);
    }

    /**
     * Base optimisée : pré-chargement des relations nécessaires pour éviter N+1
     */
    private function getOptimizedBaseQB(): QueryBuilder
    {
        $qb = $this->getQueryBuilder();
        // Relations hypothétiques : tags, categories, meta (ajuster selon mapping réel)
        $qb->leftJoin('p.tags', 't')->addSelect('t');
        if ($this->getEntityManager()->getClassMetadata(Post::class)->hasAssociation('categories')) {
            $qb->leftJoin('p.categories', 'c')->addSelect('c');
        }
        if ($this->getEntityManager()->getClassMetadata(Post::class)->hasAssociation('meta')) {
            $qb->leftJoin('p.meta', 'm')->addSelect('m');
        }

        return $qb;
    }
}
