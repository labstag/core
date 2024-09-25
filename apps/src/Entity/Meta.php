<?php

namespace Labstag\Entity;

use Stringable;
use Override;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Labstag\Repository\MetaRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: MetaRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Meta implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Chapter $chapter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Edito $edito = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?History $history = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keywords = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Page $page = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Post $post = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEdito(): ?Edito
    {
        return $this->edito;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setChapter(Chapter $chapter): static
    {
        // set the owning side of the relation if necessary
        if ($chapter->getMeta() !== $this) {
            $chapter->setMeta($this);
        }

        $this->chapter = $chapter;

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setEdito(Edito $edito): static
    {
        // set the owning side of the relation if necessary
        if ($edito->getMeta() !== $this) {
            $edito->setMeta($this);
        }

        $this->edito = $edito;

        return $this;
    }

    public function setHistory(History $history): static
    {
        // set the owning side of the relation if necessary
        if ($history->getMeta() !== $this) {
            $history->setMeta($this);
        }

        $this->history = $history;

        return $this;
    }

    public function setKeywords(?string $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function setPage(Page $page): static
    {
        // set the owning side of the relation if necessary
        if ($page->getMeta() !== $this) {
            $page->setMeta($this);
        }

        $this->page = $page;

        return $this;
    }

    public function setPost(Post $post): static
    {
        // set the owning side of the relation if necessary
        if ($post->getMeta() !== $this) {
            $post->setMeta($this);
        }

        $this->post = $post;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
