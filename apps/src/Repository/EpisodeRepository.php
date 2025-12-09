<?php

namespace Labstag\Repository;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Episode;
use Labstag\Entity\Season;

/**
 * @extends RepositoryAbstract<Episode>
 */
class EpisodeRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return Query<mixed, mixed>
     */
    public function getQueryPaginator(Season $season): Query
    {
        $data = new ArrayCollection();
        $data->add(new Parameter('enable', true));
        $data->add(new Parameter('refseason', $season));
        $data->add(new Parameter('now', new DateTimeImmutable()));

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->where('e.enable = :enable');
        $queryBuilder->andWhere('e.refseason = :refseason');
        $queryBuilder->andWhere('(e.airDate <= :now OR e.airDate IS NULL)');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('e.number', 'ASC');

        $query        = $queryBuilder->getQuery();
        $dql          = $query->getDQL();
        $query->enableResultCache(3600, 'movies-query-paginator-' . md5((string) $dql));

        return $query;
    }
}
