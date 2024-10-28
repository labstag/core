<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Repository\BlockRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block
{

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private ?bool $enable = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'block')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $paragraphs;

    #[ORM\Column(options: ['default' => 1])]
    private int $position = 1;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setBlock($this);
        }

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getBlock() === $this) {
            $paragraph->setBlock(null);
        }

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
