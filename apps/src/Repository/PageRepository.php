<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Page;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PageRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Page::class);
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('a.createdAt', 'DESC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'page-activate');

        return $query->getResult();
    }

    public function getOneBySlug(string $slug): ?Page
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.slug = :slug');
        $queryBuilder->setParameter('slug', $slug);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'page-slug');

        return $query->getOneOrNullResult();
    }

    public function getOneByType(string $type): ?Page
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.type = :type');
        $queryBuilder->setParameter('type', $type);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'page-type');

        return $query->getOneOrNullResult();
    }
}
