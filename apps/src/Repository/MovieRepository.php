<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Symfony\Component\Intl\Countries;

/**
 * @extends ServiceEntityRepositoryAbstract<Movie>
 */
class MovieRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Movie::class);
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @param array<string> $excludedImdbIds
     *
     * @return list<Movie>
     */
    public function findMoviesNotInImdbList(array $excludedImdbIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.imdb NOT IN (:imdbIds)');
        $queryBuilder->setParameter('imdbIds', $excludedImdbIds);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-not-in-imdb-list');

        return $query->getResult();
    }

    public function getAllActivateBySaga(Saga $saga): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->andWhere('m.saga = :saga');
        $queryBuilder->setParameter('saga', $saga);
        $queryBuilder->orderBy('m.title', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-activate-by-saga-' . $saga->getId());

        return $query->getResult();
    }

    public function getCertifications(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select('DISTINCT m.certification');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('m.certification', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'movies-unique-certifications');

        $data           = $query->getSingleColumnResult();
        $certifications = [];
        foreach ($data as $value) {
            if (!is_null($value) && '' !== $value) {
                $certifications[$value] = $value;
            }
        }

        return $certifications;
    }

    /**
     * @param array<string, mixed> $query
     */
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

    /**
     * @param array<string, mixed> $query
     *
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(array $query): Query
    {
        $queryBuilder = $this->getQueryBuilder($query);
        $query        = $queryBuilder->getQuery();
        $dql          = $query->getDQL();
        $query->enableResultCache(3600, 'movies-query-paginator-' . md5((string) $dql));

        return $query;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getQueryBuilderCategories(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['categories'])) {
            return;
        }

        $queryBuilder->andWhere('c.slug = :categories');
        $queryBuilder->setParameter('categories', $query['categories']);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getQueryBuilderCountry(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['country'])) {
            return;
        }

        $queryBuilder->andWhere('JSON_CONTAINS(m.countries, :country) = 1');
        $queryBuilder->setParameter('country', json_encode($query['country']));
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getQueryBuilderSaga(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['sagas'])) {
            return;
        }

        $queryBuilder->andWhere('s.slug = :sagas');
        $queryBuilder->setParameter('sagas', $query['sagas']);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getQueryBuilderTitle(QueryBuilder $queryBuilder, array $query): void
    {
        if (empty($query['title'])) {
            return;
        }

        $queryBuilder->andWhere('m.title LIKE :title');
        $queryBuilder->setParameter('title', '%' . $query['title'] . '%');
    }

    /**
     * @param array<string, mixed> $query
     */
    private function getQueryBuilderYear(QueryBuilder $queryBuilder, array $query): void
    {
        if (!isset($query['year']) || !is_numeric($query['year'])) {
            return;
        }

        $queryBuilder->andWhere('YEAR(m.releaseDate) = :year');
        $queryBuilder->setParameter('year', $query['year']);
    }
}
