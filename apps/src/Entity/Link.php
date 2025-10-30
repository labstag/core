<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\LinkRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link implements Stringable
{

    #[ORM\Column]
    protected bool $blank = false;

    #[ORM\ManyToOne(inversedBy: 'links', cascade: ['persist', 'detach'])]
    protected ?Block $block = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $classes = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[ORM\Column(length: 255)]
    protected ?string $url = null;

    #[Override]
    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isBlank(): bool
    {
        return $this->blank;
    }

    public function setBlank(bool $blank): static
    {
        $this->blank = $blank;

        return $this;
    }

    public function setBlock(?Block $block): static
    {
        $this->block = $block;

        return $this;
    }

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
