<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\History;
use Labstag\Lib\ServiceEntityRepositoryLib;

class HistoryRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, History::class);
    }

    public function findLastByNbr(int $nbr)
    {
        return $this->getQueryBuilder()->setMaxResults($nbr)->getQuery()->getResult();
    }

    public function findTotalEnable()
    {
        return $this->getQueryBuilder()->select('count(a.id)')->getQuery()->getSingleScalarResult();
    }

    public function getAllActivate()
    {
        return $this->getQueryBuilder()->getQuery()->getResult();
    }

    public function getQueryPaginator()
    {
        return $this->getQueryBuilder()->getQuery();
    }

    private function getQueryBuilder()
    {
        return $this->createQueryBuilder('a')->where('a.enable = :enable')->setParameter('enable', true)->orderBy('a.createdAt', 'DESC');
    }
}
