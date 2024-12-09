<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Star;
use Labstag\Lib\ServiceEntityRepositoryLib;

class StarRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Star::class);
    }

    public function getQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('p.title', 'ASC');
    }

    public function getQueryPaginator()
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }
}
