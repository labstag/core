<?php

namespace Labstag\Repository;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Episode;
use Labstag\Entity\Season;

/**
 * @extends ServiceEntityRepositoryAbstract<Episode>
 */
class EpisodeRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function getAllActivateBySeason(Season $season): mixed
    {
        $data = new ArrayCollection();
        $data->add(new Parameter('enable', true));
        $data->add(new Parameter('refseason', $season));
        $data->add(new Parameter('now', new DateTimeImmutable()));

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->where('e.enable = :enable');
        $queryBuilder->andWhere('e.airDate <= :now');
        $queryBuilder->andWhere('e.refseason = :refseason');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('e.number', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'episodes-activate-season-' . $season->getId());

        return $query->getResult();
    }
}
