<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ExperiencesParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?array $experiences = null;

    public function getExperiences(): ?array
    {
        return $this->experiences;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setExperiences(?array $experiences): static
    {
        $this->experiences = $experiences;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
