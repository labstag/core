<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\MetaRepository;
use Labstag\Traits\Entity\TimestampableTrait;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Override;

#[ORM\Entity(repositoryClass: MetaRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Meta implements \Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Chapter $chapter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keywords = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Page $page = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Post $post = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'])]
    private ?Story $story = null;

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

    public function getStory(): ?Story
    {
        return $this->story;
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

    public function setStory(Story $story): static
    {
        // set the owning side of the relation if necessary
        if ($story->getMeta() !== $this) {
            $story->setMeta($this);
        }

        $this->story = $story;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
