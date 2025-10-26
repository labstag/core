<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Serie;
use Symfony\Component\Intl\Countries;

/**
 * @extends ServiceEntityRepositoryAbstract<Serie>
 */
class SerieRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Serie::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function findAllUniqueCountries(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->select('DISTINCT s.countries');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('s.countries', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'series-unique-countries');

        $data    = $query->getSingleColumnResult();
        $country = [];
        foreach ($data as $value) {
            if (!is_null($value)) {
                $country = array_merge($country, json_decode((string) $value));
            }
        }

        $country = array_unique($country);
        sort($country, SORT_STRING);
        $data    = $country;
        $country = [];
        foreach ($data as $value) {
            $country[Countries::getName($value)] = $value;
        }

        return $country;
    }

    /**
     * @return array<string, mixed>
     */
    public function findAllUniqueYear(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->select('DISTINCT YEAR(s.releaseDate)');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('s.releaseDate', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'series-unique-releaseDate');

        return $query->getSingleColumnResult();
    }

    public function findAllUpdate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->orderBy('s.title', 'ASC');

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

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->leftJoin('s.categories', 'c')->addSelect('c');

        return $queryBuilder->orderBy('s.title', 'ASC');
    }

    /**
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(): Query
    {
        $queryBuilder = $this->getQueryBuilder();
        $query        = $queryBuilder->getQuery();
        $dql          = $query->getDQL();
        $query->enableResultCache(3600, 'series-query-paginator-' . md5((string) $dql));

        return $query;
    }
}
