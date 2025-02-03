<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\GeoCode;
use Labstag\Lib\ServiceEntityRepositoryLib;

class GeoCodeRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, GeoCode::class);
    }

    public function findAllData(string $type): mixed
    {
        $queryBuilder = $this->createQueryBuilder('g');

        $query = $queryBuilder->select('g.' . $type . ', count(g.id) as count');
        $query->groupBy('g.' . $type);

        return $query->getQuery()->getResult();
    }
}
