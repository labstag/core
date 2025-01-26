<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Movie;
use Labstag\Lib\ServiceEntityRepositoryLib;

class MovieRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Movie::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('s.title', 'ASC');
    }

    public function findLastByNbr(int $nbr): mixed
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        $queryBuilder->orderBy('s.createdAt', 'DESC');
        $queryBuilder->setMaxResults($nbr);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }
}
