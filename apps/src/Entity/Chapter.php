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
use Labstag\Repository\ChapterRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
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

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $img = null;

    #[Vich\UploadableField(mapping: 'chapter', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    #[ORM\OneToOne(inversedBy: 'chapter', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Meta $meta = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'chapter', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    protected Collection $paragraphs;

    #[ORM\Column(
        options: ['default' => 1]
    )]
    protected int $position = 1;

    #[ORM\ManyToOne(inversedBy: 'chapters', cascade: ['persist', 'detach'])]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Story $refstory = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $resume = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    public function __construct()
    {
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
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getChapter() === $this
        ) {
            $paragraph->setChapter(null);
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
