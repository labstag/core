<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Submission;

/**
 * @extends ServiceEntityRepositoryAbstract<Submission>
 */
class SubmissionRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Submission::class);
    }
}
