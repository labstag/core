<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

/**
 * @extends ServiceEntityRepositoryLib<Paragraph>
 */
class ParagraphRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Paragraph::class);
    }
}
