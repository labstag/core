<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Category;

/**
 * @extends RepositoryAbstract<Category>
 */
class CategoryRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Category::class);
    }

    /**
     * @return array<Category>
     */
    public function findAllByTypeMovie(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->orderBy('c.title', 'ASC');

        $query = $queryBuilder->getQuery();
        // Ne pas mettre de cache

        return $query->getResult();
    }

    /**
     * @return array<Category>
     */
    public function findAllByTypeMovieEnable(string $class): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->resetDQLPart('from');
        $queryBuilder->from($class, 'c');
        $queryBuilder->orderBy('c.title', 'ASC');
        $queryBuilder->leftJoin('c.movies', 'm')->addSelect('m');
        $queryBuilder->andWhere('m.enable = true');
        dump(get_class_methods($queryBuilder));
        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'category-type-movie-enable');
        return $query->getResult();
    }

    /**
     * @return array<Category>
     */
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

    /**
     * @return array<Category>
     */
    public function findAllByTypeSerie(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'serie');
        $queryBuilder->orderBy('c.title', 'ASC');

        $query = $queryBuilder->getQuery();
        // Ne pas mettre de cache

        return $query->getResult();
    }

    /**
     * @return array<Category>
     */
    public function findAllByTypeSerieEnable(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'serie');
        $queryBuilder->orderBy('c.title', 'ASC');
        $queryBuilder->leftJoin('c.series', 's')->addSelect('s');
        $queryBuilder->andWhere('s.enable = true');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'category-type-serie-enable');

        return $query->getResult();
    }

    /**
     * @return array<Category>
     */
    public function findAllByTypeSerieWithoutSerie(): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'serie');
        $queryBuilder->orderBy('c.title', 'ASC');
        $queryBuilder->leftJoin('c.series', 's')->addSelect('s');
        $queryBuilder->andWhere('s.id IS NULL');

        $query = $queryBuilder->getQuery();
        // Ne pas mettre de cache

        return $query->getResult();
    }
}
