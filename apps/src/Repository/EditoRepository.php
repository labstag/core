<?php

declare(strict_types=1);

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Edito;
use SortDirection;

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
        $queryBuilder->orderBy('e.createdAt', SortDirection::Descending);
        $queryBuilder->setMaxResults(1);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'edito-last');

        return $query->getOneOrNullResult();
    }
}
