<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\BanIp;

/**
 * @extends ServiceEntityRepositoryAbstract<BanIp>
 */
class BanIpRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, BanIp::class);
    }
}
