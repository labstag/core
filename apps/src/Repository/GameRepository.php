<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Game;

/**
 * @extends RepositoryAbstract<Game>
 */
class GameRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function getAllActivate(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('g');
        $queryBuilder->where('g.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->orderBy('g.createdAt', 'DESC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'games-activate');

        return $query->getResult();
    }

    public function getAllIgdb(): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('p.igdb');

        $result = $queryBuilder->getQuery()->getArrayResult();

        return array_column($result, 'igdb');
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getQueryBuilder(array $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('g');
        $queryBuilder->where('g.enable = :enable');
        $queryBuilder->setParameter('enable', true);
        $queryBuilder->leftJoin('g.categories', 'c')->addSelect('c');

        return $queryBuilder->orderBy('g.' . $query['order'], $query['orderby']);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(array $query, ?string $categorySlug): Query
    {
        $queryBuilder = $this->getQueryBuilder($query);
        if ('' != $categorySlug) {
            $queryBuilder->andWhere('c.slug = :categorySlug');
            $queryBuilder->setParameter('categorySlug', $categorySlug);
        }

        $query        = $queryBuilder->getQuery();
        $dql          = $query->getDQL();
        $query->enableResultCache(3600, 'movies-query-paginator-' . md5((string) $dql));

        return $query;
    }
}
