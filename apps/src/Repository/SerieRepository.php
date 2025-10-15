<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Serie;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Serie>
 */
class SerieRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Serie::class);
    }

    public function findAllUpdate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $query        = $queryBuilder->getQuery();

        return $query->getResult();
    }

    /**
     * @param array<string> $excludedImdbIds
     *
     * @return list<Serie>
     */
    public function findSeriesNotInImdbList(array $excludedImdbIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.imdb NOT IN (:imdbIds)');
        $queryBuilder->setParameter('imdbIds', $excludedImdbIds);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'series-not-in-imdb-list');

        return $query->getResult();
    }
}
