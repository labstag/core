<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FormationsParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
