<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Configuration;

/**
 * @extends RepositoryAbstract<Configuration>
 */
class ConfigurationRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Configuration::class);
    }
}
