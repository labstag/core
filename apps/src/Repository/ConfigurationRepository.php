<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Configuration;

/**
 * @extends ServiceEntityRepositoryAbstract<Configuration>
 */
class ConfigurationRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Configuration::class);
    }
}
