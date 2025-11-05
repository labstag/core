<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FormationsParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?array $formations = null;

    public function getFormations(): ?array
    {
        return $this->formations;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setFormations(?array $formations): static
    {
        $this->formations = $formations;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
