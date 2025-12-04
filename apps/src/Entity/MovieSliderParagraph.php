<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MovieSliderParagraph extends Paragraph
{
    #[ORM\Column(nullable: true)]
    protected ?int $nbr = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    public function getNbr(): ?int
    {
        return $this->nbr;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setNbr(?int $nbr): static
    {
        $this->nbr = $nbr;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
