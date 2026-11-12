<?php

namespace Labstag\Repository;

use Labstag\Entity\Episode;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Casting;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use SortDirection;

/**
 * @extends RepositoryAbstract<Season>
 */
class SeasonRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function delete(object $entity): void
    {
        if ($entity instanceof Season) {
            $filters    = $this->getEntityManager()->getFilters();
            $wasEnabled = $filters->has('softdeleteable') && $filters->isEnabled('softdeleteable');
            if ($wasEnabled) {
                $filters->disable('softdeleteable');
            }

            $episodes = $this->getEntityManager()->getRepository(Episode::class)->findBy(
                ['refseason' => $entity]
            );
            foreach ($episodes as $episode) {
                $episodeCastings = $this->getEntityManager()->getRepository(Casting::class)->findBy(
                    ['refEpisode' => $episode]
                );
                foreach ($episodeCastings as $casting) {
                    $this->getEntityManager()->remove($casting);
                }

                $this->getEntityManager()->flush();
                $this->getEntityManager()->remove($episode);
            }

            $castings = $this->getEntityManager()->getRepository(Casting::class)->findBy(
                ['refSeason' => $entity]
            );
            foreach ($castings as $casting) {
                $this->getEntityManager()->remove($casting);
            }

            $this->getEntityManager()->flush();

            if ($wasEnabled && $filters->has('softdeleteable') && !$filters->isEnabled('softdeleteable')) {
                $filters->enable('softdeleteable');
            }
        }

        parent::delete($entity);
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
        $queryBuilder->andWhere('(s.airDate <= :now OR s.airDate IS NULL)');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('s.number', SortDirection::Ascending);

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'seasons-activate-serie-' . $serie->getId());

        return $query->getResult();
    }

    public function getdeleteTrashBySerie(Serie $serie): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->where('s.refserie = :refserie');
        $queryBuilder->andWhere('s.deletedAt IS NOT NULL');
        $queryBuilder->setParameter('refserie', $serie);
        return $queryBuilder->getQuery()->getResult();
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

    public function remove(object $entity): void
    {
        if ($entity instanceof Season) {
            $episodes = $this->getEntityManager()->getRepository(Labstag\Entity\Episode::class)->findBy(
                ['refseason' => $entity]
            );
            foreach ($episodes as $episode) {
                $this->getEntityManager()->getRepository(Labstag\Entity\Episode::class)->remove($episode);
            }

            $castings = $this->getEntityManager()->getRepository(Casting::class)->findBy(
                ['refSeason' => $entity]
            );
            foreach ($castings as $casting) {
                $this->getEntityManager()->remove($casting);
            }

            $this->getEntityManager()->flush();
        }

        parent::remove($entity);
    }
}
