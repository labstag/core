<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\GeoCode;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

class GeoCodeRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, GeoCode::class);
    }

    public function findAllData(string $type): mixed
    {
        $queryBuilder = $this->createQueryBuilder('g');

        $queryBuilder->select('g.' . $type . ', count(g.id) as count');
        $queryBuilder->groupBy('g.' . $type);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'geocode-' . md5($type));

        return $query->getResult();
    }
}
