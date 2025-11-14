<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MovieInfoParagraph extends Paragraph
{

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Movie $refmovie = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    public function getRefmovie(): ?Movie
    {
        return $this->refmovie;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setRefmovie(?Movie $movie): static
    {
        $this->refmovie = $movie;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
