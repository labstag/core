<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Redirection;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Redirection>
 */
class RedirectionRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Redirection::class);
    }
}
