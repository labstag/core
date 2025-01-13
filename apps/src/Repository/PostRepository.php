<?php

namespace Labstag\Repository;

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

        return $query->getResult();
    }

    public function findTotalEnable(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(p.id)');

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('p.createdAt', 'DESC');
    }

    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }
}
