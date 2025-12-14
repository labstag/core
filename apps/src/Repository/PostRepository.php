<?php

namespace Labstag\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;

/**
 * @extends RepositoryAbstract<Post>
 */
class PostRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Post::class);
    }

    public function findLastByNbr(int $nbr): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setMaxResults($nbr);

        $query = $queryBuilder->getQuery();

        $query->enableResultCache(600, 'post-last-' . $nbr);

        return $query->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('count(p.id)');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->andWhere('p.createdAt <= :now');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->setParameter('now', new DateTime('now'));

        $query = $queryBuilder->getQuery();

        $query->enableResultCache(900, 'post-total-enable');

        return $query->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $query        = $queryBuilder->getQuery();
        $query->enableResultCache(600, 'post-activate');

        return $query->getResult();
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
    public function getQueryPaginator(?string $categorySlug, ?string $tagSlug): Query
    {
        $queryBuilder = $this->getQueryBuilder();
        if ($categorySlug) {
            $queryBuilder->leftJoin('p.categories', 'c')->addSelect('c');
            $queryBuilder->andWhere('c.slug = :categorySlug');
            $queryBuilder->setParameter('categorySlug', $categorySlug);
        }

        if ($tagSlug) {
            $queryBuilder->leftJoin('p.tags', 't')->addSelect('t');
            $queryBuilder->andWhere('t.slug = :tagSlug');
            $queryBuilder->setParameter('tagSlug', $tagSlug);
        }

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(300, 'post-query-paginator-' . $categorySlug . '-' . $tagSlug);

        return $query;
    }
}
