<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

abstract class Content
{
    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[Gedmo\Slug(updatable: false, fields: ['title'])]
    #[ORM\Column(length: 255)]
    protected ?string $slug = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}