<?php

namespace Labstag\Repository;

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

    public function getAllIgdb(): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('p.igdb');

        $result = $queryBuilder->getQuery()->getArrayResult();

        return array_column($result, 'igdb');
    }
}
