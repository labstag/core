<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\BanIp;

/**
 * @extends RepositoryAbstract<BanIp>
 */
class BanIpRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, BanIp::class);
    }

    public function findOlderThanOneDay(): array
    {
        $oneDayAgo = new \DateTime('-1 day');
                
        $queryBuilder = $this->createQueryBuilder('b');
        $queryBuilder->where('b.createdAt <= :oneDayAgo');
        $queryBuilder->setParameter('oneDayAgo', $oneDayAgo);

        return $queryBuilder->getQuery()->getResult();
    }
}
