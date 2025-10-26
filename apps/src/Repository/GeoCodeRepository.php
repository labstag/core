<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\GeoCode;

/**
 * @extends ServiceEntityRepositoryAbstract<GeoCode>
 */
class GeoCodeRepository extends ServiceEntityRepositoryAbstract
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
