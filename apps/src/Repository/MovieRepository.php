<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Movie;
use Labstag\Lib\ServiceEntityRepositoryLib;

class MovieRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Movie::class);
    }

    public function findAllUniqueCountries(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select('DISTINCT m.country');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('m.country', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getSingleColumnResult();
    }

    public function findAllUniqueYear(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select('DISTINCT m.year');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('m.year', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getSingleColumnResult();
    }

    public function findTrailerImageDescriptionIsNull(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->leftJoin('m.saga', 's');
        $queryBuilder->where('m.trailer IS NULL');
        $queryBuilder->orWhere('m.img IS NULL');
        $queryBuilder->orWhere('s.slug is NULL');
        $queryBuilder->orWhere('s.slug = :slug');
        $queryBuilder->setParameter('slug', '');
        $queryBuilder->orWhere('m.description IS NULL');
        $queryBuilder->orWhere('m.evaluation IS NULL');
        $queryBuilder->orWhere('m.votes IS NULL');

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

        return $query->getResult();
    }

    public function getQueryBuilder(array $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        if (isset($query['title']) && !empty($query['title'])) {
            $queryBuilder->andWhere('m.title LIKE :title');
            $queryBuilder->setParameter('title', '%' . $query['title'] . '%');
        }

        if (isset($query['country']) && !empty($query['country'])) {
            $queryBuilder->andWhere('m.country = :country');
            $queryBuilder->setParameter('country', $query['country']);
        }

        if (isset($query['categories']) && !empty($query['categories'])) {
            $queryBuilder->leftJoin('m.categories', 'c');
            $queryBuilder->andWhere('c.slug = :categories');
            $queryBuilder->setParameter('categories', $query['categories']);
        }

        if (isset($query['sagas']) && !empty($query['sagas'])) {
            $queryBuilder->leftJoin('m.saga', 's');
            $queryBuilder->andWhere('s.slug = :sagas');
            $queryBuilder->setParameter('sagas', $query['sagas']);
        }

        if (isset($query['year']) && !empty($query['year'])) {
            $queryBuilder->andWhere('m.year = :year');
            $queryBuilder->setParameter('year', $query['year']);
        }

        return $queryBuilder->orderBy('m.' . $query['order'], $query['orderby']);
    }

    public function findMoviesNotInImdbList(array $excludedImdbIds): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.imdb NOT IN (:imdbIds)');
        $queryBuilder->setParameter('imdbIds', $excludedImdbIds);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getQueryPaginator(array $query): Query
    {
        $queryBuilder = $this->getQueryBuilder($query);

        return $queryBuilder->getQuery();
    }
}
