<?php

declare(strict_types=1);

namespace Labstag\Entity;

use SortDirection;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LinksBlock extends Block
{

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    #[ORM\Column(nullable: true)]
    private ?array $links = null;

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getLinks(): ?array
    {
        return $this->links;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function setLinks(?array $links): static
    {
        $this->links = $links;

        return $this;
    }
}
