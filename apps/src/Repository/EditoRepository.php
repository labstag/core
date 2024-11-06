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
        $query = $this->createQueryBuilder('a')->where('a.enable = :enable')->setParameter('enable', true)->orderBy('a.createdAt', 'DESC')->setMaxResults(1)->getQuery();

        return $query->getOneOrNullResult();
    }
}
