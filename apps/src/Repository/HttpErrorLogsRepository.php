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

    public function getAllinternetProtocolWithNbr(int $nbr): array
    {
        $queryBuilder = $this->createQueryBuilder('hel');
        $queryBuilder->select('hel.internetProtocol, COUNT(hel.internetProtocol) AS nbr');
        $queryBuilder->groupBy('hel.internetProtocol');
        $queryBuilder->having('nbr >= :nbr');
        $queryBuilder->setParameter('nbr', $nbr);
        $queryBuilder->andWhere('hel.refuser IS NULL');
        $queryBuilder->orderBy('nbr', 'DESC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'http-error-logs-ip-nbr-' . $nbr);

        return $query->getResult();
    }
}
