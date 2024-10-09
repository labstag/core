<?php

namespace Labstag\Repository\Paragraph;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Paragraph\Form;
use Labstag\Lib\ServiceEntityRepositoryLib;

class FormRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Form::class);
    }
}
