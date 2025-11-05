<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph;

/**
 * @extends RepositoryAbstract<Paragraph>
 */
class ParagraphRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Paragraph::class);
    }

    public function findOnByType(string $class): ?Paragraph
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p INSTANCE OF :class');
        $queryBuilder->setParameter('class', $class);
        $queryBuilder->setMaxResults(1);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'paragraph-' . $class);

        return $query->getOneOrNullResult();
    }
}
