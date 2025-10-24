<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Season>
 */
class SeasonRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function getAllActivateBySerie(Serie $serie): mixed
    {
        $data = new ArrayCollection([new Parameter('enable', true), new Parameter('refserie', $serie)]);

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->andWhere('s.refserie = :refserie');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('s.number', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'seasons-activate-serie-' . $serie->getId());

        return $query->getResult();
    }
}
