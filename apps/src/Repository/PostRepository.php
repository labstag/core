<?php

namespace Labstag\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PostRepository extends ServiceEntityRepositoryLib
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
        $query->enableResultCache(3600, 'post-last-'.$nbr);

        return $query->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(p.id)');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'post-total-enable');

        return $query->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $query        = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'post-activate');

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

    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'post-query-paginator');

        return $query;
    }
}
