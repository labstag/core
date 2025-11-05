<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\BanIp;

/**
 * @extends RepositoryAbstract<BanIp>
 */
class BanIpRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, BanIp::class);
    }
}
