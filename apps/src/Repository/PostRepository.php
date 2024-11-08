<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PostRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Post::class);
    }

    public function findLastByNbr(int $nbr)
    {
        return $this->getQueryBuilder()->setMaxResults($nbr)->getQuery()->getResult();
    }

    public function findTotalEnable()
    {
        return $this->getQueryBuilder()->select('count(a.id)')->getQuery()->getSingleScalarResult();
    }

    public function getQueryBuilder()
    {
        return $this->createQueryBuilder('a')->where('a.enable = :enable')->setParameter('enable', true)->orderBy('a.createdAt', 'DESC');
    }

    public function getQueryPaginator()
    {
        return $this->getQueryBuilder()->getQuery();
    }
}
