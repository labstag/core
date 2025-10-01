<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Category;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

class CategoryRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Category::class);
    }

    public function findAllByTypeMovie(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'movie');
        $queryBuilder->orderBy('c.title', 'ASC');

        $query = $queryBuilder->getQuery();
        // Ne pas mettre de cache

        return $query->getResult();
    }

    public function findAllByTypeMovieEnable(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'movie');
        $queryBuilder->orderBy('c.title', 'ASC');
        $queryBuilder->leftJoin('c.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.enable = true');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'category-type-movie-enable');

        return $query->getResult();
    }

    public function findAllByTypeMovieWithoutMovie(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'movie');
        $queryBuilder->orderBy('c.title', 'ASC');
        $queryBuilder->leftJoin('c.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.id IS NULL');

        $query = $queryBuilder->getQuery();
        // Ne pas mettre de cache

        return $query->getResult();
    }
}
