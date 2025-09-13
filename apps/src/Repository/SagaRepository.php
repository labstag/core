<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Saga;
use Labstag\Lib\ServiceEntityRepositoryLib;

class SagaRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Saga::class);
    }

    public function findAllByTypeMovie(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->orderBy('s.title', 'ASC');
        $queryBuilder->leftJoin('s.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.enable = true');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'saga-type-movie');

        return $query->getResult();
    }
}
