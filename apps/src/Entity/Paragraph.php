<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Labstag\Entity\Paragraph\Form;
use Labstag\Entity\Paragraph\Html;
use Labstag\Entity\Paragraph\Image;
use Labstag\Entity\Paragraph\Text;
use Labstag\Entity\Paragraph\Video;
use Labstag\Repository\ParagraphRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: ParagraphRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Paragraph
{
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Chapter $chapter = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Edito $edito = null;

    #[ORM\Column(
        type: 'boolean',
        options: ['default' => 1]
    )]
    private ?bool $enable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fond = null;

    #[ORM\OneToOne(inversedBy: 'paragraph', cascade: ['persist', 'remove'])]
    private ?Form $form = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?History $history = null;

    #[ORM\OneToOne(inversedBy: 'paragraph', cascade: ['persist', 'remove'])]
    private ?Html $html = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToOne(inversedBy: 'paragraph', cascade: ['persist', 'remove'])]
    private ?Image $image = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Memo $memo = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'paragraphs')]
    private ?Post $post = null;

    #[ORM\OneToOne(inversedBy: 'paragraph', cascade: ['persist', 'remove'])]
    private ?Text $text = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\OneToOne(inversedBy: 'paragraph', cascade: ['persist', 'remove'])]
    private ?Video $video = null;

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function getEdito(): ?Edito
    {
        return $this->edito;
    }

    public function getFond(): ?string
    {
        return $this->fond;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function getHtml(): ?Html
    {
        return $this->html;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImage(): ?Image
    {
        return $this->image;
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

    public function getText(): ?Text
    {
        return $this->text;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
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

    public function setForm(?Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function setHistory(?History $history): static
    {
        $this->history = $history;

        return $this;
    }

    public function setHtml(?Html $html): static
    {
        $this->html = $html;

        return $this;
    }

    public function setImage(?Image $image): static
    {
        $this->image = $image;

        return $this;
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

    public function setText(?Text $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setVideo(?Video $video): static
    {
        $this->video = $video;

        return $this;
    }
}
