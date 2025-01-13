<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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

    public function getAllActivateByStory(Story $story): mixed
    {
        $data = new ArrayCollection([new Parameter('enable', true), new Parameter('refstory', $story)]);

        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.enable = :enable');
        $queryBuilder->andWhere('a.refstory = :refstory');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('a.position', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
