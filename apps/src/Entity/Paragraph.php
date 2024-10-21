<?php

namespace Labstag\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Labstag\Repository\ParagraphRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ParagraphRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
class Paragraph implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Chapter $chapter = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Edito $edito = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private ?bool $enable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fond = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?History $history = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'paragraph', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Memo $memo = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Post $post = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getType();
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getEdito(): ?Edito
    {
        return $this->edito;
    }

    public function getFond(): ?string
    {
        return $this->fond;
    }

    public function getHistory(): ?History
    {
        return $this->history;
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

    public function getMemo(): ?Memo
    {
        return $this->memo;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getPost(): ?Post
    {
        return $this->post;
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

    public function setChapter(?Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function setEdito(?Edito $edito): static
    {
        $this->edito = $edito;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setFond(?string $fond): static
    {
        $this->fond = $fond;

        return $this;
    }

    public function setHistory(?History $history): static
    {
        $this->history = $history;

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
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function setMemo(?Memo $memo): static
    {
        $this->memo = $memo;

        return $this;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function setTitle(?string $title): static
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
