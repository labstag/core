<?php

declare(strict_types=1);

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Submission;

/**
 * @extends RepositoryAbstract<Submission>
 */
class SubmissionRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Submission::class);
    }
}
