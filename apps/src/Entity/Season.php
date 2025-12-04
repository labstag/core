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
use Labstag\Repository\SeasonRepository;
use Labstag\SlugHandler\SeasonSlugHandler;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
class Season implements Stringable, EntityWithParagraphsInterface
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    public $backdrop;

    public $backdropFile;

    #[ORM\Column(name: 'air_date', type: Types::DATE_MUTABLE, nullable: true)]
    protected ?DateTime $airDate = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: [
            'default' => 1,
        ]
    )]
    protected ?bool $enable = null;

    /**
     * @var Collection<int, Episode>
     */
    #[ORM\OneToMany(targetEntity: Episode::class, mappedBy: 'refseason', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        [
            'number' => 'ASC',
        ]
    )]
    protected Collection $episodes;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\OneToOne(inversedBy: 'season', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Meta $meta = null;

    #[ORM\Column(nullable: true)]
    protected ?int $number = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $overview = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'season', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        [
            'position' => 'ASC',
        ]
    )]
    protected Collection $paragraphs;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $poster = null;

    #[Vich\UploadableField(mapping: 'season', fileNameProperty: 'poster')]
    protected ?File $posterFile = null;

    #[ORM\ManyToOne(inversedBy: 'seasons')]
    #[ORM\JoinColumn(name: 'refserie_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Serie $refserie = null;

    #[Gedmo\Slug(fields: ['title'], updatable: true, unique: false)]
    #[Gedmo\SlugHandler(class: SeasonSlugHandler::class)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $tmdb = null;

    #[ORM\Column(name: 'vote_average', nullable: true)]
    protected ?float $voteAverage = null;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
        $this->episodes   = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addEpisode(Episode $episode): static
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes->add($episode);
            $episode->setRefseason($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setSeason($this);
        }

        return $this;
    }

    public function getAirDate(): ?DateTime
    {
        return $this->airDate;
    }

    public function getBackdrop(): ?string
    {
        return $this->backdrop;
    }

    public function getBackdropFile(): ?File
    {
        return $this->backdropFile;
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function getPosterFile(): ?File
    {
        return $this->posterFile;
    }

    public function getRefserie(): ?Serie
    {
        return $this->refserie;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTmdb(): ?string
    {
        return $this->tmdb;
    }

    public function getVoteAverage(): ?float
    {
        return $this->voteAverage;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function removeEpisode(Episode $episode): static
    {
        // set the owning side to null (unless already changed)
        if ($this->episodes->removeElement($episode) && $episode->getRefseason() === $this
        ) {
            $episode->setRefseason(null);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getSeason() === $this
        ) {
            $paragraph->setSeason(null);
        }

        return $this;
    }

    public function setAirDate(?DateTime $airDate): static
    {
        $this->airDate = $airDate;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function setOverview(?string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function setPoster(?string $poster): void
    {
        $this->poster = $poster;

        if (null === $poster) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setPosterFile(?File $posterFile = null): void
    {
        $this->posterFile = $posterFile;

        if ($posterFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setRefserie(?Serie $serie): static
    {
        $this->refserie = $serie;

        return $this;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setTmdb(?string $tmdb): static
    {
        $this->tmdb = $tmdb;

        return $this;
    }

    public function setVoteAverage(?float $voteAverage): static
    {
        $this->voteAverage = $voteAverage;

        return $this;
    }
}
