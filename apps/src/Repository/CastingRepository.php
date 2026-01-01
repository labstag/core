<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Casting;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Person;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;

/**
 * @extends RepositoryAbstract<Casting>
 */
class CastingRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Casting::class);
    }
    
    public function findWithActiveCastings(mixed $data): mixed
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->innerJoin('c.refPerson', 'p');
        $queryBuilder->addSelect('p');
        $queryBuilder->andWhere('p.deletedAt IS NULL');
        $entityMap = [
            Person::class  => ['refPerson', 'person'],
            Movie::class   => ['refMovie', 'movie'],
            Serie::class   => ['refSerie', 'serie'],
            Season::class  => ['refSeason', 'season'],
            Episode::class => ['refEpisode', 'episode'],
        ];
        foreach ($entityMap as $class => [$field, $param]) {
            if ($data instanceof $class) {
                $queryBuilder->andWhere("c.{$field} = :{$param}");
                $queryBuilder->setParameter($param, $data);
                break;
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
