<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Chapter>
 */
class ChapterRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Chapter::class);
    }

    public function getAllActivateByStory(Story $story): mixed
    {
        $data = new ArrayCollection();
        $data->add(new Parameter('enable', true));
        $data->add(new Parameter('refstory', $story));

        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->where('c.enable = :enable');
        $queryBuilder->andWhere('c.refstory = :refstory');
        $queryBuilder->setParameters($data);
        $queryBuilder->orderBy('c.position', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'chapter-activate-story-' . $story->getId());

        return $query->getResult();
    }
}
