<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Block;

/**
 * @extends RepositoryAbstract<Block>
 */
class BlockRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Block::class);
    }

    public function findAllOrderedByRegion(?QueryBuilder $queryBuilder): void
    {
        if (!$queryBuilder instanceof \Doctrine\ORM\QueryBuilder) {
            $queryBuilder = $this->createQueryBuilder('b');
        }

        $alias = $queryBuilder->getRootAliases()[0] ?? 'entity';
        $queryBuilder->resetDQLPart('orderBy');
        $caseExpr = 'CASE '
            . 'WHEN ' . $alias . ".region = 'header' THEN 1 "
            . 'WHEN ' . $alias . ".region = 'main' THEN 2 "
            . 'WHEN ' . $alias . ".region = 'footer' THEN 3 "
            . 'ELSE 4 '
            . 'END';
        $queryBuilder->orderBy($caseExpr, 'ASC');
        $queryBuilder->addOrderBy($alias . '.position', 'ASC');
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
