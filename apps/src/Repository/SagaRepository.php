<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Saga;
use Labstag\Lib\ServiceEntityRepositoryLib;

class SagaRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Saga::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.movies', 'movies')->addSelect('movies');

        return $queryBuilder;

    }

    public function findAllByTypeMovie(): array
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->orderBy('s.title', 'ASC');
        $queryBuilder->andWhere('movies.enable = true');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
