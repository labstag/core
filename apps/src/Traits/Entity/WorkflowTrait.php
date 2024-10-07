<?php

namespace Labstag\Traits\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait WorkflowTrait
{

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $currentPlace = [];

    public function getCurrentPlace(): array
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(array $currentPlace, array $context = []): void
    {
        unset($context);
        $this->currentPlace = $currentPlace;
    }
}
