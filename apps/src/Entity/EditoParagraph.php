<?php

declare(strict_types=1);

namespace Labstag\Entity;

use SortDirection;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EditoParagraph extends Paragraph
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
