<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Memo;
use Labstag\Lib\ServiceEntityRepositoryLib;

class MemoRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Memo::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->leftJoin('m.paragraphs', 'paragraphs')->addSelect('paragraphs');
        $queryBuilder->leftJoin('m.refuser', 'refuser')->addSelect('refuser');

        return $queryBuilder;

    }
}
