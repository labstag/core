<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Lib\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepository<HttpErrorLogs>
 */
class HttpErrorLogsRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HttpErrorLogs::class);
    }
}
