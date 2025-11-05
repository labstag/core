<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Repository\BlockRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Table(name: 'block')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Entity(repositoryClass: BlockRepository::class)]
#[ORM\Index(name: 'IDX_BLOCK_SLUG', columns: ['slug'])]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(
    [
        'admin'      => AdminBlock::class,
        'paragraphs' => ParagraphsBlock::class,
        'links'      => LinksBlock::class,
        'html'       => HtmlBlock::class,
        'hero'       => HeroBlock::class,
        'flashbag'   => FlashbagBlock::class,
        'content'    => ContentBlock::class,
        'breadcrumb' => BreadcrumbBlock::class,
    ]
)]
abstract class Block implements Stringable
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $classes = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $pages = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'block', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    protected Collection $paragraphs;

    #[ORM\Column(
        options: ['default' => 1]
    )]
    protected int $position = 1;

    #[ORM\Column(length: 255)]
    protected ?string $region = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $requestPath = false;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?array $roles = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(length: 255, unique: true)]
    protected string $slug;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setBlock($this);
        }

        return $this;
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function getId(): ?string
    {
        return $this->id;
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

    /**
     * @return string[]|null
     */
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

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function isRequestPath(): ?bool
    {
        return $this->requestPath;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getBlock() === $this
        ) {
            $paragraph->setBlock(null);
        }

        return $this;
    }

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

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

    /**
     * @param string[]|null $roles
     */
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
}
