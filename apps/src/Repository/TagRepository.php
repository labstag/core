<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Tag;

/**
 * @extends ServiceEntityRepositoryAbstract<Tag>
 */
class TagRepository extends ServiceEntityRepositoryAbstract
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Tag::class);
    }
}
