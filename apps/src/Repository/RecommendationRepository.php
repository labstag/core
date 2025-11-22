<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Recommendation;

/**
 * @extends RepositoryAbstract<Recommendation>
 */
class RecommendationRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recommendation::class);
    }
}
