<?php

namespace Labstag\Repository;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;

/**
 * @extends RepositoryAbstract<Season>
 */
class SeasonRepository extends RepositoryAbstract
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

    public function getOneBySerieAndPosition(Serie $serie, int $position): ?Season
    {
        $data = new ArrayCollection();
        $data->add(new Parameter('enable', true));
        $data->add(new Parameter('refserie', $serie));
        $data->add(new Parameter('number', $position));
        $data->add(new Parameter('now', new DateTimeImmutable()));

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.enable = :enable');
        $queryBuilder->andWhere('s.refserie = :refserie');
        $queryBuilder->andWhere('s.number = :number');
        $queryBuilder->andWhere('s.airDate <= :now');
        $queryBuilder->setParameters($data);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'season-serie-' . $serie->getId() . '-position-' . $position);

        return $query->getOneOrNullResult();
    }
}
