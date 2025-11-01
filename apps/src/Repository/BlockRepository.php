<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Block;

/**
 * @extends ServiceEntityRepositoryAbstract<Block>
 */
class BlockRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Block::class);
    }

    public function findAllOrderedByRegion(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('b');
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
        $queryBuilder = $this->createQueryBuilder('b');
        $queryBuilder->select('MAX(b.position) as maxposition');
        $queryBuilder->where('b.region = :region');
        $queryBuilder->setParameter('region', $region);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'block-maxposition-' . md5($region));

        $data = $query->getOneOrNullResult();

        return is_array($data) ? $data['maxposition'] : null;
    }
}
