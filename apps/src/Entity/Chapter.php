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
use Labstag\Repository\ChapterRepository;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Entity\Traits\WorkflowTrait;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Table(indexes: [new ORM\Index(name: 'IDX_CHAPTER_SLUG', columns: ['slug'])])]
class Chapter implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;
    use WorkflowTrait;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'chapter', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\OneToOne(inversedBy: 'chapter', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meta $meta = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'chapter', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    private Collection $paragraphs;

    #[ORM\Column(
        options: ['default' => 1]
    )]
    private int $position = 1;

    #[ORM\ManyToOne(inversedBy: 'chapters', cascade: ['persist', 'detach'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Story $refstory = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resume = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'chapters', cascade: ['persist', 'detach'])]
    private Collection $tags;

    public function __construct()
    {
        $this->tags       = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setChapter($this);
        }

        return $this;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addChapter($this);
        }

        return $this;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getRefstory(): ?Story
    {
        return $this->refstory;
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

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getChapter() === $this) {
            $paragraph->setChapter(null);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeChapter($this);
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

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setRefstory(?Story $story): static
    {
        $this->refstory = $story;

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
