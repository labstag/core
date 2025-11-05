<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CompetencesParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?array $competences = null;

    public function getCompetences(): ?array
    {
        return $this->competences;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setCompetences(?array $competences): static
    {
        $this->competences = $competences;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
