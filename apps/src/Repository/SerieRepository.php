<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Serie;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;
use Symfony\Component\Intl\Countries;

/**
 * @extends ServiceEntityRepositoryLib<Serie>
 */
class SerieRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Serie::class);
    }
}
