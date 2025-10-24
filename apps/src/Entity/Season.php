<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\RelativeSlugHandler;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\SeasonRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[Vich\Uploadable]
class Season
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $slug = null;

    #[ORM\Column(name: 'air_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $airDate = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'season', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[ORM\Column(nullable: true)]
    private ?int $number = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\ManyToOne(inversedBy: 'seasons')]
    private ?Serie $refserie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tmdb = null;

    #[ORM\Column(name: 'vote_average', nullable: true)]
    private ?float $voteAverage = null;

    #[ORM\OneToOne(inversedBy: 'season', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meta $meta = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'season', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    private Collection $paragraphs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    /**
     * @var Collection<int, Episode>
     */
    #[ORM\OneToMany(targetEntity: Episode::class, mappedBy: 'refseason')]
    #[ORM\OrderBy(
        ['number' => 'ASC']
    )]
    private Collection $episodes;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
        $this->episodes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

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

    /**
     * @return Collection<int, Paragraph>
     */
    public function getParagraphs(): Collection
    {
        return $this->paragraphs;
    }

    public function getAirDate(): ?DateTime
    {
        return $this->airDate;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function getRefserie(): ?Serie
    {
        return $this->refserie;
    }

    public function getTmdb(): ?string
    {
        return $this->tmdb;
    }

    public function getVoteAverage(): ?float
    {
        return $this->voteAverage;
    }

    public function setAirDate(?DateTime $airDate): static
    {
        $this->airDate = $airDate;

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

    public function setRefserie(?Serie $serie): static
    {
        $this->refserie = $serie;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): static
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes->add($episode);
            $episode->setRefseason($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): static
    {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getRefseason() === $this) {
                $episode->setRefseason(null);
            }
        }

        return $this;
    }
}
