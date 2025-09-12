<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ServiceEntityRepositoryLib;

class ParagraphRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Paragraph::class);
    }

    private function getCreateQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin('p.block', 'block')->addSelect('block');
        $queryBuilder->leftJoin('p.chapter', 'chapter')->addSelect('chapter');
        $queryBuilder->leftJoin('p.edito', 'edito')->addSelect('edito');
        $queryBuilder->leftJoin('p.memo', 'memo')->addSelect('memo');
        $queryBuilder->leftJoin('p.page', 'page')->addSelect('page');
        $queryBuilder->leftJoin('p.post', 'post')->addSelect('post');
        $queryBuilder->leftJoin('p.story', 'story')->addSelect('story');

        return $queryBuilder;

    }
}
