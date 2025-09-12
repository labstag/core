<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Lib\ServiceEntityRepositoryLib;

class HttpErrorLogsRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, HttpErrorLogs::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('h');
        $queryBuilder->leftJoin('h.refuser', 'refuser')->addSelect('refuser');

        return $queryBuilder;

    }
}
