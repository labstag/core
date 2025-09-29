<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Movie;
use Labstag\Lib\ServiceEntityRepositoryLib;
use Symfony\Component\Intl\Countries;

class MovieRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Movie::class);
    }

    public function findAllUniqueCountries(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select('DISTINCT m.countries');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('m.countries', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-unique-countries');

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

    public function findAllUniqueYear(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select('DISTINCT YEAR(m.releaseDate)');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('m.releaseDate', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-unique-releaseDate');

        return $query->getSingleColumnResult();
    }

    public function findAllUpdate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->leftJoin('m.saga', 's')->addSelect('s');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findLastByNbr(int $nbr): mixed
    {
        $query = [
            'order'   => 'createdAt',
            'orderby' => 'DESC',
        ];
        $queryBuilder = $this->getQueryBuilder($query);
        $queryBuilder->setMaxResults($nbr);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-last-' . $nbr);

        return $query->getResult();
    }

    public function findMoviesNotInImdbList(array $excludedImdbIds): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.imdb NOT IN (:imdbIds)');
        $queryBuilder->setParameter('imdbIds', $excludedImdbIds);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-not-in-imdb-list');

        return $query->getResult();
    }

    public function getQueryBuilder(array $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->leftJoin('m.categories', 'c')->addSelect('c');
        $queryBuilder->leftJoin('m.saga', 's')->addSelect('s');
        $this->getQueryBuilderTitle($queryBuilder, $query);
        $this->getQueryBuilderCountry($queryBuilder, $query);
        $this->getQueryBuilderSaga($queryBuilder, $query);
        $this->getQueryBuilderCategories($queryBuilder, $query);
        $this->getQueryBuilderYear($queryBuilder, $query);

        return $queryBuilder->orderBy('m.' . $query['order'], $query['orderby']);
    }

    public function getQueryPaginator(array $query): Query
    {
        $queryBuilder = $this->getQueryBuilder($query);
        $query        = $queryBuilder->getQuery();
        $dql          = $query->getDQL();
        $query->enableResultCache(3600, 'movies-query-paginator-' . md5((string) $dql));

        return $query;
    }

    private function getQueryBuilderCategories(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['categories'])) {
            return;
        }

        $queryBuilder->andWhere('c.slug = :categories');
        $queryBuilder->setParameter('categories', $query['categories']);
    }

    private function getQueryBuilderCountry(QueryBuilder $queryBuilder, array $query): void
    {
        array_flip($this->findAllUniqueCountries());
        if (empty($query['country'])) {
            return;
        }

        $queryBuilder->andWhere('JSON_CONTAINS(m.countries, :country) = 1');
        $queryBuilder->setParameter('country', json_encode($query['country']));
    }

    private function getQueryBuilderSaga(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['sagas'])) {
            return;
        }

        $queryBuilder->andWhere('s.slug = :sagas');
        $queryBuilder->setParameter('sagas', $query['sagas']);
    }

    private function getQueryBuilderTitle(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['title'])) {
            return;
        }

        $queryBuilder->andWhere('m.title LIKE :title');
        $queryBuilder->setParameter('title', '%' . $query['title'] . '%');
    }

    private function getQueryBuilderYear(QueryBuilder $queryBuilder, array $query): void
    {
        if (!isset($query['year']) || !is_numeric($query['year'])) {
            return;
        }

        $queryBuilder->andWhere('YEAR(m.releaseDate) = :year');
        $queryBuilder->setParameter('year', $query['year']);
    }
}
