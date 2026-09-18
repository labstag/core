<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SeasonListParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
