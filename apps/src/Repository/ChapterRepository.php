<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Lib\ServiceEntityRepositoryLib;

class ChapterRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Chapter::class);
    }

    public function getAllActivateByHistory(History $history)
    {
        $data = new ArrayCollection(
            [
                new Parameter('enable', true),
                new Parameter('refhistory', $history),
            ]
        );

        return $this->createQueryBuilder('a')->where('a.enable = :enable')->andWhere('a.refhistory = :refhistory')->setParameters($data)->orderBy('a.position', 'ASC')->getQuery()->getResult();
    }
}
