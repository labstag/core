<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Star;
use Labstag\Lib\ServiceEntityRepositoryLib;

class StarRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Star::class);
    }

    public function findAllLanguage()
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $query = $queryBuilder->select('s.language, count(s.id) as count');
        $query->groupBy('s.language');

        return $query->getQuery()->getResult();
    }

    public function findAllLicense()
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $query = $queryBuilder->select('s.license, count(s.id) as count');
        $query->groupBy('s.license');

        return $query->getQuery()->getResult();
    }

    public function getQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('s.title', 'ASC');
    }

    public function getQueryPaginator()
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }
}
