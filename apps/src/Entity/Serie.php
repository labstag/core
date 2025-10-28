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
use Labstag\Repository\SerieRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: SerieRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Index(name: 'IDX_SERIE_SLUG', columns: ['slug'])]
class Serie implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column]
    private ?bool $adult = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'series', cascade: ['persist', 'detach'])]
    private Collection $categories;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certification = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $citation = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $countries = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private ?bool $enable = null;

    #[ORM\Column(nullable: true)]
    private ?float $evaluation = null;

    #[ORM\Column]
    private ?bool $file = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imdb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'serie', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\Column(nullable: true)]
    private ?bool $inProduction = null;

    #[ORM\Column(name: 'lastrelease_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $lastreleaseDate = null;

    #[ORM\OneToOne(inversedBy: 'serie', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meta $meta = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'serie', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    private Collection $paragraphs;

    #[ORM\Column(name: 'release_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $releaseDate = null;

    /**
     * @var Collection<int, Season>
     */
    #[ORM\OneToMany(targetEntity: Season::class, mappedBy: 'refserie', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['number' => 'ASC']
    )]
    private Collection $seasons;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tmdb = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $trailer = null;

    #[ORM\Column(nullable: true)]
    private ?int $votes = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->seasons    = new ArrayCollection();
        $this->paragraphs = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addSerie($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setSerie($this);
        }

        return $this;
    }

    public function addSeason(Season $season): static
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons->add($season);
            $season->setRefserie($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function getCertification(): ?string
    {
        return $this->certification;
    }

    public function getCitation(): ?string
    {
        return $this->citation;
    }

    /**
     * @return string[]|null
     */
    public function getCountries(): ?array
    {
        return $this->countries;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEvaluation(): ?float
    {
        return $this->evaluation;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImdb(): ?string
    {
        return $this->imdb;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function getLastreleaseDate(): ?DateTime
    {
        return $this->lastreleaseDate;
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

    public function getReleaseDate(): ?DateTime
    {
        return $this->releaseDate;
    }

    /**
     * @return Collection<int, Season>
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
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

    public function getTrailer(): ?string
    {
        return $this->trailer;
    }

    public function getVotes(): ?int
    {
        return $this->votes;
    }

    public function isAdult(): ?bool
    {
        return $this->adult;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function isFile(): ?bool
    {
        return $this->file;
    }

    public function isInProduction(): ?bool
    {
        return $this->inProduction;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeSerie($this);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getStory() === $this
        ) {
            $paragraph->setStory(null);
        }

        return $this;
    }

    public function removeSeason(Season $season): static
    {
        // set the owning side to null (unless already changed)
        if ($this->seasons->removeElement($season) && $season->getRefserie() === $this
        ) {
            $season->setRefserie(null);
        }

        return $this;
    }

    public function setAdult(bool $adult): static
    {
        $this->adult = $adult;

        return $this;
    }

    public function setCertification(?string $certification): static
    {
        $this->certification = $certification;

        return $this;
    }

    public function setCitation(?string $citation): static
    {
        $this->citation = $citation;

        return $this;
    }

    /**
     * @param string[]|null $countries
     */
    public function setCountries(?array $countries): static
    {
        $this->countries = $countries;

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setEvaluation(?float $evaluation): static
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    public function setFile(bool $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function setImdb(?string $imdb): static
    {
        $this->imdb = $imdb;

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

    public function setInProduction(?bool $inProduction): static
    {
        $this->inProduction = $inProduction;

        return $this;
    }

    public function setLastreleaseDate(?DateTime $lastreleaseDate): static
    {
        $this->lastreleaseDate = $lastreleaseDate;

        return $this;
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function setReleaseDate(?DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

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

    public function setTrailer(?string $trailer): static
    {
        $this->trailer = $trailer;

        return $this;
    }

    public function setVotes(?int $votes): static
    {
        $this->votes = $votes;

        return $this;
    }
}
