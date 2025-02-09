<?php

namespace Labstag\Traits\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait WorkflowTrait
{

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $state = ['start'];

    #[Gedmo\Timestampable(on: 'change', field: ['state'])]
    #[ORM\Column(name: 'state_changed', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $stateChanged = null;

    /**
     * @return mixed[]
     */
    public function getState(): array
    {
        return $this->state;
    }

    public function getStateChanged(): ?DateTime
    {
        return $this->stateChanged;
    }

    /**
     * @param mixed[] $state
     * @param mixed[] $context
     */
    public function setState(array $state, array $context = []): void
    {
        unset($context);
        $this->state = $state;
    }
}
