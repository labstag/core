<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
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
    use TimestampableTrait;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Block $block = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Chapter $chapter = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $classes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Edito $edito = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected bool $enable = true;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $fond = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $form = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $img = null;

    #[Vich\UploadableField(mapping: 'paragraph', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $leftposition = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Memo $memo = null;

    #[ORM\Column(nullable: true)]
    protected ?int $nbr = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Page $page = null;

    #[ORM\Column]
    protected ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    protected ?Movie $refmovie = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Saga $saga = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected bool $save = true;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Season $season = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Serie $serie = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs', cascade: ['persist', 'detach'])]
    protected ?Story $story = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(length: 255)]
    protected ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

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

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getEdito(): ?Edito
    {
        return $this->edito;
    }

    public function getFond(): ?string
    {
        return $this->fond;
    }

    public function getForm(): ?string
    {
        return $this->form;
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

    public function getRefmovie(): ?Movie
    {
        return $this->refmovie;
    }

    public function getSaga(): ?Serie
    {
        return $this->saga;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
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

    public function isLeftposition(): ?bool
    {
        return $this->leftposition;
    }

    public function isSave(): ?bool
    {
        return $this->save;
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

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

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

    public function setForm(?string $form): static
    {
        $this->form = $form;

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

    public function setLeftposition(?bool $leftposition): static
    {
        $this->leftposition = $leftposition;

        return $this;
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

    public function setRefmovie(?Movie $movie): static
    {
        $this->refmovie = $movie;

        return $this;
    }

    public function setSaga(?Saga $saga): static
    {
        $this->saga = $saga;

        return $this;
    }

    public function setSave(bool $save): static
    {
        $this->save = $save;

        return $this;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function setSerie(?Serie $serie): static
    {
        $this->serie = $serie;

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
