<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Page;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Page>
 */
class PageRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Page::class);
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('p.createdAt', 'DESC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'page-activate');

        return $query->getResult();
    }

    public function getOneBySlug(string $slug): ?Page
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.slug = :slug');
        $queryBuilder->setParameter('slug', $slug);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'page-slug_' . md5($slug));

        return $query->getOneOrNullResult();
    }

    public function getOneByType(string $type): ?Page
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.type = :type');
        $queryBuilder->setParameter('type', $type);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'page-type_' . md5($type));

        return $query->getOneOrNullResult();
    }
}
