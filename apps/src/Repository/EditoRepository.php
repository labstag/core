<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Edito;
use Labstag\Lib\ServiceEntityRepositoryLib;

class EditoRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Edito::class);
    }

    public function findLast()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('a.createdAt', 'DESC');
        $queryBuilder->setMaxResults(1);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }
}
