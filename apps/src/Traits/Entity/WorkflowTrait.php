<?php

namespace Labstag\Traits\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait WorkflowTrait
{

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $stage = [];

    #[Gedmo\Timestampable(on: 'change', field: ['stage'])]
    #[ORM\Column(name: 'stage_changed', type: 'datetime', nullable: true)]
    private DateTime $stateChanged;

    public function getStage(): array
    {
        return $this->stage;
    }

    public function getStateChanged(): DateTime
    {
        return $this->stateChanged;
    }

    public function setStage(array $stage, array $context = []): void
    {
        unset($context);
        $this->stage = $stage;
    }
}
