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
}
