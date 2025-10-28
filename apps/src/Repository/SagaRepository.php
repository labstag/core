<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Saga;

/**
 * @extends ServiceEntityRepositoryAbstract<Saga>
 */
class SagaRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Saga::class);
    }

    /**
     * @return array<Saga>
     */
    public function findAllByTypeMovieEnable(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->orderBy('s.title', 'ASC');
        $queryBuilder->leftJoin('s.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.enable = true');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'saga-type-movie');

        return $query->getResult();
    }

    /**
     * @return array<Saga>
     */
    public function findSagaWithoutMovie(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.id IS NULL');
        $queryBuilder->orderBy('s.title', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
