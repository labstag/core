<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Tag;

/**
 * @extends RepositoryAbstract<Tag>
 */
class TagRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Tag::class);
    }

    public function findByType(?QueryBuilder $queryBuilder, string $class): void
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            $queryBuilder = $this->createQueryBuilder('b');
        }

        $alias = $queryBuilder->getRootAliases()[0] ?? 'entity';
        $queryBuilder->resetDQLPart('from');
        $queryBuilder->from($class, $alias);
    }
}
