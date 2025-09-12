<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Tag;
use Labstag\Lib\ServiceEntityRepositoryLib;

class TagRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Tag::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->leftJoin('t.chapters', 'chapters')->addSelect('chapters');
        $queryBuilder->leftJoin('t.pages', 'pages')->addSelect('pages');
        $queryBuilder->leftJoin('t.posts', 'posts')->addSelect('posts');
        $queryBuilder->leftJoin('t.stories', 'stories')->addSelect('stories');
        $queryBuilder->leftJoin('t.movies', 'movies')->addSelect('movies');

        return $queryBuilder;

    }
}
