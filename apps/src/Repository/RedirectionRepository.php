<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Redirection;

/**
 * @extends ServiceEntityRepositoryAbstract<Redirection>
 */
class RedirectionRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Redirection::class);
    }
}
