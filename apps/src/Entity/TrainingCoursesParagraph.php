<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TrainingCoursesParagraph extends Paragraph
{
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?array $trainings = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTrainings(): ?array
    {
        return $this->trainings;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setTrainings(?array $trainings): static
    {
        $this->trainings = $trainings;

        return $this;
    }
}
