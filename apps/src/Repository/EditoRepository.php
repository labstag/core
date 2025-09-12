<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Edito;
use Labstag\Lib\ServiceEntityRepositoryLib;

class EditoRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Edito::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->leftJoin('e.paragraphs', 'paragraphs')->addSelect('paragraphs');
        $queryBuilder->leftJoin('e.refuser', 'refuser')->addSelect('refuser');

        return $queryBuilder;

    }

    public function findLast(): mixed
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->where('e.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('e.createdAt', 'DESC');
        $queryBuilder->setMaxResults(1);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }
}
