<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LinksBlock extends Block
{

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
