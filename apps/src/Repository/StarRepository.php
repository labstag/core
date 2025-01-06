<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Star;
use Labstag\Lib\ServiceEntityRepositoryLib;

class StarRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Star::class);
    }

    public function findAllData(string $type): mixed
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $query = $queryBuilder->select('s.'.$type.', count(s.id) as count');
        $query->groupBy('s.'.$type);

        return $query->getQuery()->getResult();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);

        return $queryBuilder->orderBy('s.title', 'ASC');
    }

    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();

        return $queryBuilder->getQuery();
    }
}
