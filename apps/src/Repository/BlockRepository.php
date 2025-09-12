<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Block;
use Labstag\Lib\ServiceEntityRepositoryLib;

class BlockRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Block::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $queryBuilder->leftJoin('b.links', 'links')->addSelect('links');
        $queryBuilder->leftJoin('b.paragraphs', 'paragraphs')->addSelect('paragraphs');

        return $queryBuilder;
    }

    public function findAllOrderedByRegion(): QueryBuilder
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->orderBy(
            "CASE 
                WHEN b.region = 'header' THEN 1
                WHEN b.region = 'main' THEN 2
                WHEN b.region = 'footer' THEN 3
                ELSE 4
            END",
            'ASC'
        );
        $queryBuilder->addOrderBy('b.position', 'ASC');

        return $queryBuilder;
    }

    public function getMaxPositionByRegion(string $region): ?int
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->select('MAX(b.position) as maxposition');
        $queryBuilder->where('b.region = :region');
        $queryBuilder->setParameter('region', $region);

        $query = $queryBuilder->getQuery();
        $data  = $query->getOneOrNullResult();

        return is_array($data) ? $data['maxposition'] : null;
    }
}
