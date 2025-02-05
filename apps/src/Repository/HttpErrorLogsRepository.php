<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Lib\ServiceEntityRepositoryLib;

class HttpErrorLogsRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, HttpErrorLogs::class);
    }
}
