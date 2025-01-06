<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\LinkRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link implements Stringable
{

    #[ORM\Column]
    private bool $blank = false;

    #[ORM\ManyToOne(inversedBy: 'links', cascade: ['persist', 'detach'])]
    private ?Block $block = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $classes = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

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
