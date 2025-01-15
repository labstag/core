<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Submission;
use Labstag\Lib\ServiceEntityRepositoryLib;

class SubmissionRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submission::class);
    }
}
