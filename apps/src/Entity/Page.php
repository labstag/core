<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Entity\Traits\WorkflowTrait;
use Labstag\Repository\PageRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Index(name: 'IDX_PAGE_SLUG', columns: ['slug'])]
class Page implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;
    use WorkflowTrait;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[Gedmo\SlugHandler(
        class: TreeSlugHandler::class,
        options: [
            'parentRelationField' => 'page',
            'separator'           => '/',
        ]
    )]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(
        targetEntity: Category::class,
        mappedBy: 'pages',
        cascade: [
            'persist',
            'detach',
        ]
    )]
    private Collection $categories;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(
        targetEntity: self::class,
        mappedBy: 'page',
        cascade: [
            'persist',
            'detach',
        ]
    )]
    private Collection $children;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'page', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\OneToOne(inversedBy: 'page', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meta $meta = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children', cascade: ['persist', 'detach'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?self $page = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(
        targetEntity: Paragraph::class,
        mappedBy: 'page',
        cascade: [
            'persist',
            'remove',
        ]
    )]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    private Collection $paragraphs;

    #[ORM\ManyToOne(inversedBy: 'pages', cascade: ['persist', 'detach'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $refuser = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resume = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(
        targetEntity: Tag::class,
        mappedBy: 'pages',
        cascade: [
            'persist',
            'detach',
        ]
    )]
    private Collection $tags;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    public function __construct()
    {
        $this->children   = new ArrayCollection();
        $this->tags       = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addPage($this);
        }

        return $this;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setPage($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setPage($this);
        }

        return $this;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addPage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getPage(): ?self
    {
        return $this->page;
    }

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
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

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removePage($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement(
            $child
        ) && $child->getPage() === $this
        ) {
            $child->setPage(null);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement(
            $paragraph
        ) && $paragraph->getPage() === $this
        ) {
            $paragraph->setPage(null);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removePage($this);
        }

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setImg(?string $img): void
    {
        $this->img = $img;

        // Si l'image est supprimée (img devient null), on force la mise à jour
        if (null === $img) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setImgFile(?File $imgFile = null): void
    {
        $this->imgFile = $imgFile;

        if ($imgFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(
                new DateTimeImmutable()
            );
        }
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function setPage(?self $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function setRefuser(?User $user): static
    {
        $this->refuser = $user;

        return $this;
    }

    public function setResume(?string $resume): static
    {
        $this->resume = $resume;

        return $this;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
