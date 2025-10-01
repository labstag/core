<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Meta;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

class MetaRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Meta::class);
    }
}
