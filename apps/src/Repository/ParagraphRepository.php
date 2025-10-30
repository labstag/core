<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph;

/**
 * @extends ServiceEntityRepositoryAbstract<Paragraph>
 */
class ParagraphRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Paragraph::class);
    }
}
