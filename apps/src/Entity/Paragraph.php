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
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ParagraphRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
class Paragraph implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Block $block = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Chapter $chapter = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Edito $edito = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private ?bool $enable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fond = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'paragraph', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Memo $memo = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbr = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    private ?Story $story = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getType();
    }

    public function getBlock(): ?Block
    {
        return $this->block;
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

    public function getId(): ?int
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

    public function getNbr(): ?int
    {
        return $this->nbr;
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

    public function getStory(): ?Story
    {
        return $this->story;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setBlock(?Block $block): static
    {
        $this->block = $block;

        return $this;
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

    public function setNbr(?int $nbr): static
    {
        $this->nbr = $nbr;

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

    public function setStory(?Story $story): static
    {
        $this->story = $story;

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

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
