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
}
