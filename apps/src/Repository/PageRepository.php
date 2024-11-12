<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Page;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PageRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Page::class);
    }

    public function getAllActivate()
    {
        return $this->createQueryBuilder('a')->where('a.enable = :enable')->setParameter('enable', true)->orderBy('a.createdAt', 'DESC')->getQuery()->getResult();
    }
}
