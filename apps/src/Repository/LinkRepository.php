<?php

namespace Labstag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Link;

/**
 * @extends ServiceEntityRepository<Link>
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Link::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->leftJoin('l.block', 'block')->addSelect('block');

        return $queryBuilder;

    }
}
