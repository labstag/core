<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\MetaRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: MetaRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Meta implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Chapter $chapter = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $description = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Game $game = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $keywords = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Movie $movie = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Page $page = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Person $person = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Post $post = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Saga $saga = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Season $season = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Serie $serie = null;

    #[ORM\OneToOne(mappedBy: 'meta', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Story $story = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function getSaga(): ?Saga
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

    public function setGame(Game $game): static
    {
        // set the owning side of the relation if necessary
        if ($game->getMeta() !== $this) {
            $game->setMeta($this);
        }

        $this->game = $game;

        return $this;
    }

    public function setKeywords(?string $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function setMovie(Movie $movie): static
    {
        // set the owning side of the relation if necessary
        if ($movie->getMeta() !== $this) {
            $movie->setMeta($this);
        }

        $this->movie = $movie;

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

    public function setPerson(Person $person): static
    {
        // set the owning side of the relation if necessary
        if ($person->getMeta() !== $this) {
            $person->setMeta($this);
        }

        $this->person = $person;

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

    public function setSaga(Saga $saga): static
    {
        // set the owning side of the relation if necessary
        if ($saga->getMeta() !== $this) {
            $saga->setMeta($this);
        }

        $this->saga = $saga;

        return $this;
    }

    public function setSeason(Season $season): static
    {
        // set the owning side of the relation if necessary
        if ($season->getMeta() !== $this) {
            $season->setMeta($this);
        }

        $this->season = $season;

        return $this;
    }

    public function setSerie(Serie $serie): static
    {
        // set the owning side of the relation if necessary
        if ($serie->getMeta() !== $this) {
            $serie->setMeta($this);
        }

        $this->serie = $serie;

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
