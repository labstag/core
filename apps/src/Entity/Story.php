<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Entity\Traits\WorkflowTrait;
use Labstag\Repository\StoryRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: StoryRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Index(name: 'IDX_STORY_SLUG', columns: ['slug'])]
class Story implements Stringable, EntityWithParagraphsInterface
{
    use SoftDeleteableEntity;
    use TimestampableTrait;
    use WorkflowTrait;

    /**
     * @var Collection<int, StoryCategory>
     */
    #[ORM\ManyToMany(targetEntity: StoryCategory::class, mappedBy: 'stories', cascade: ['persist', 'detach'])]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    protected Collection $categories;

    /**
     * @var Collection<int, Chapter>
     */
    #[ORM\OneToMany(
        targetEntity: Chapter::class,
        mappedBy: 'refstory',
        cascade: [
            'persist',
            'remove',
        ],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    protected Collection $chapters;

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

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $img = null;

    #[Vich\UploadableField(mapping: 'story', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    #[ORM\OneToOne(inversedBy: 'story', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Meta $meta = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(
        targetEntity: Paragraph::class,
        mappedBy: 'story',
        cascade: [
            'persist',
            'remove',
        ],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    protected Collection $paragraphs;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $pdf = null;

    #[Vich\UploadableField(mapping: 'story', fileNameProperty: 'pdf')]
    protected ?File $pdfFile = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'stories')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?User $refuser = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $resume = null;

    #[Gedmo\Slug(fields: ['title'], updatable: true)]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    protected ?string $slug = null;

    /**
     * @var Collection<int, StoryTag>
     */
    #[ORM\ManyToMany(targetEntity: StoryTag::class, mappedBy: 'stories', cascade: ['persist', 'detach'])]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    protected Collection $tags;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    public function __construct()
    {
        $this->chapters   = new ArrayCollection();
        $this->tags       = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addCategory(StoryCategory $storyCategory): static
    {
        if (!$this->categories->contains($storyCategory)) {
            $this->categories->add($storyCategory);
            $storyCategory->addStory($this);
        }

        return $this;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setRefstory($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setStory($this);
        }

        return $this;
    }

    public function addTag(StoryTag $storyTag): static
    {
        if (!$this->tags->contains($storyTag)) {
            $this->tags->add($storyTag);
            $storyTag->addStory($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, StoryCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
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

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function getPdfFile(): ?File
    {
        return $this->pdfFile;
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
     * @return Collection<int, StoryTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function removeCategory(StoryCategory $storyCategory): static
    {
        if ($this->categories->removeElement($storyCategory)) {
            $storyCategory->removeStory($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        // set the owning side to null (unless already changed)
        if ($this->chapters->removeElement($chapter) && $chapter->getRefstory() === $this
        ) {
            $chapter->setRefstory(null);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getStory() === $this
        ) {
            $paragraph->setStory(null);
        }

        return $this;
    }

    public function removeTag(StoryTag $storyTag): static
    {
        if ($this->tags->removeElement($storyTag)) {
            $storyTag->removeStory($this);
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
    }

    public function setImgFile(?File $imgFile = null): void
    {
        $this->imgFile = $imgFile;

        if ($imgFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function setPdf(?string $pdf): void
    {
        $this->pdf = $pdf;
    }

    public function setPdfFile(?File $pdfFile = null): void
    {
        $this->pdfFile = $pdfFile;

        if ($pdfFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
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
}
