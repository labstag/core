<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Memo;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

class MemoRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Memo::class);
    }
}
