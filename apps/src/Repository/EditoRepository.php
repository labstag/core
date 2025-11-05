<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Edito;

/**
 * @extends RepositoryAbstract<Edito>
 */
class EditoRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Edito::class);
    }

    public function findLast(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->where('e.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('e.createdAt', 'DESC');
        $queryBuilder->setMaxResults(1);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'edito-last');

        return $query->getOneOrNullResult();
    }
}
