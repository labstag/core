<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PostRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Post::class);
    }

    public function findLastByNbr(int $nbr)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->setMaxResults($nbr);
        $query = $queryBuilder->getQuery();
        
        return $query->getResult();
    }

    public function findTotalEnable()
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('count(a.id)');
        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getAllActivate()
    {
        $queryBuilder = $this->getQueryBuilder();
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        
        return $queryBuilder->orderBy('a.createdAt', 'DESC');
    }

    public function getQueryPaginator()
    {
        $queryBuilder = $this->getQueryBuilder();
        
        return $queryBuilder->getQuery();
    }
}
