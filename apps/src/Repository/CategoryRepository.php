<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Category;
use Labstag\Lib\ServiceEntityRepositoryLib;

class CategoryRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Category::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->leftJoin('c.movies', 'movies')->addSelect('movies');
        $queryBuilder->leftJoin('c.pages', 'pages')->addSelect('pages');
        $queryBuilder->leftJoin('c.posts', 'posts')->addSelect('posts');
        $queryBuilder->leftjoin('c.stories', 'stories')->addSelect('stories');
        $queryBuilder->leftJoin('c.parent', 'parent')->addSelect('parent');
        $queryBuilder->leftJoin('c.children', 'children')->addSelect('children');

        return $queryBuilder;
    }

    public function findAllByTypeMovie(): array
    {
        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->andWhere('c.type = :type');
        $queryBuilder->setParameter('type', 'movie');
        $queryBuilder->orderBy('c.title', 'ASC');
        $queryBuilder->andWhere('movies.enable = true');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
