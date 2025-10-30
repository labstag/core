<?php

namespace Labstag\Repository;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;

/**
 * @extends ServiceEntityRepositoryAbstract<Season>
 */
class SeasonRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function getAllActivateBySerie(Serie $serie): mixed
    {
        $data = new ArrayCollection();
        $data->add(new Parameter('enable', true));
        $data->add(new Parameter('refserie', $serie));
        $data->add(new Parameter('now', new DateTimeImmutable()));

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->andWhere('s.refserie = :refserie');
        $queryBuilder->andWhere('s.airDate <= :now');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('s.number', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'seasons-activate-serie-' . $serie->getId());

        return $query->getResult();
    }
}
