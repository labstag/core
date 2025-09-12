<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Labstag\Lib\ServiceEntityRepositoryLib;

class ChapterRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Chapter::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->leftJoin('a.paragraphs', 'paragraphs')->addSelect('paragraphs');
        $queryBuilder->leftJoin('a.refstory', 'refstory')->addSelect('refstory');
        $queryBuilder->leftJoin('a.tags', 'tags')->addSelect('tags');

        return $queryBuilder;

    }

    public function getAllActivateByStory(Story $story): mixed
    {
        $data = new ArrayCollection(
            [
                new Parameter('enable', true),
                new Parameter('idrefstory', $story->getId())
            ]
        );

        $queryBuilder = $this->getCreateQueryBuilder();
        $queryBuilder->where('a.enable = :enable');
        $queryBuilder->andWhere('refstory.id = :idrefstory');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('a.position', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
