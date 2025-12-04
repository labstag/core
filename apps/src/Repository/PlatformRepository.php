<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Game;
use Labstag\Entity\Platform;

/**
 * @extends ServiceRepositoryAbstractEntityRepository<Platform>
 */
class PlatformRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Platform::class);
    }

    public function getAllIgdb(): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('p.igdb');

        $result = $queryBuilder->getQuery()
            ->getArrayResult();

        return array_column($result, 'igdb');
    }

    public function notInGame(Game $game): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where(':game NOT MEMBER OF p.games');
        $queryBuilder->setParameter('game', $game);
        $queryBuilder->orderBy('p.title', 'ASC');

        return $queryBuilder->getQuery()
            ->getResult();
    }
}
