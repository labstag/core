<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Repository\BlockRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block implements Stringable
{

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private ?bool $enable = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    /**
     * @var Collection<int, Link>
     */
    #[ORM\OneToMany(targetEntity: Link::class, mappedBy: 'block', cascade: ['persist', 'remove'])]
    private Collection $links;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pages = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'block', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    private Collection $paragraphs;

    #[ORM\Column(
        options: ['default' => 1]
    )]
    private int $position = 1;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $requestPath = false;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $roles = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $classes = null;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
        $this->links      = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addLink(Link $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setBlock($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setBlock($this);
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function getPages(): ?string
    {
        return $this->pages;
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

    public function getRoles(): ?array
    {
        return $this->roles;
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

    public function isRequestPath(): ?bool
    {
        return $this->requestPath;
    }

    public function removeLink(Link $link): static
    {
        // set the owning side to null (unless already changed)
        if ($this->links->removeElement($link) && $link->getBlock() === $this) {
            $link->setBlock(null);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getBlock() === $this) {
            $paragraph->setBlock(null);
        }

        return $this;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setPages(?string $pages): static
    {
        $this->pages = $pages;

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

    public function setRequestPath(bool $requestPath): static
    {
        $this->requestPath = $requestPath;

        return $this;
    }

    public function setRoles(?array $roles): static
    {
        $this->roles = $roles;

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

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

        return $this;
    }
}
