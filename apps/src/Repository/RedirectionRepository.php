<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Redirection;
use Labstag\Lib\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepository<Redirection>
 */
class RedirectionRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Redirection::class);
    }
}
