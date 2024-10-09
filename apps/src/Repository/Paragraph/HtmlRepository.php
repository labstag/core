<?php

namespace Labstag\Repository\Paragraph;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph\Html;
use Labstag\Lib\ServiceEntityRepositoryLib;

class HtmlRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Html::class);
    }
}
