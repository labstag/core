<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Episode;
use Labstag\Entity\Season;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Episode>
 */
class EpisodeRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function getAllActivateBySeason(Season $season): mixed
    {
        $data = new ArrayCollection([new Parameter('enable', true), new Parameter('refseason', $season)]);

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->where('e.enable = :enable');
        $queryBuilder->andWhere('e.refseason = :refseason');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('e.number', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'episodes-activate-season-' . $season->getId());

        return $query->getResult();
    }
}
