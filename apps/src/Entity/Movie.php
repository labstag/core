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
use Labstag\Repository\MovieRepository;
use Labstag\SlugHandler\MovieSlugHandler;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Index(name: 'IDX_MOVIE_SLUG', columns: ['slug'])]
class Movie implements Stringable, EntityWithParagraphsInterface
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Column]
    protected ?bool $adult = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $backdrop = null;

    #[Vich\UploadableField(mapping: 'movie', fileNameProperty: 'backdrop')]
    protected ?File $backdropFile = null;

    /**
     * @var Collection<int, MovieCategory>
     */
    #[ORM\ManyToMany(targetEntity: MovieCategory::class, mappedBy: 'movies', cascade: ['persist', 'detach'])]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    protected Collection $categories;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $certification = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $citation = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(nullable: true)]
    protected ?array $countries = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(nullable: true)]
    protected ?int $duration = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[ORM\Column(nullable: true)]
    protected ?float $evaluation = null;

    #[ORM\Column]
    protected ?bool $file = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255, unique: true)]
    protected ?string $imdb = null;

    #[ORM\OneToOne(inversedBy: 'movie', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Meta $meta = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(targetEntity: Paragraph::class, mappedBy: 'movie', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    protected Collection $paragraphs;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $poster = null;

    #[Vich\UploadableField(mapping: 'movie', fileNameProperty: 'poster')]
    protected ?File $posterFile = null;

    #[ORM\Column(name: 'release_date', type: Types::DATE_MUTABLE, nullable: true)]
    protected ?DateTime $releaseDate = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    protected ?Saga $saga = null;

    #[Gedmo\Slug(fields: ['title'], updatable: true, unique: false)]
    #[Gedmo\SlugHandler(class: MovieSlugHandler::class)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $tmdb = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $trailer = null;

    #[ORM\Column(nullable: true)]
    protected ?int $votes = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, mappedBy: 'movies')]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    private Collection $companies;

    /**
     * @var Collection<int, Recommendation>
     */
    #[ORM\OneToMany(targetEntity: Recommendation::class, mappedBy: 'refmovie')]
    private Collection $recommendations;

    public function __construct()
    {
        $this->categories      = new ArrayCollection();
        $this->paragraphs      = new ArrayCollection();
        $this->companies       = new ArrayCollection();
        $this->recommendations = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addCategory(MovieCategory $movieCategory): static
    {
        if (!$this->categories->contains($movieCategory)) {
            $this->categories->add($movieCategory);
            $movieCategory->addMovie($this);
        }

        return $this;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->addMovie($this);
        }

        return $this;
    }

    public function addParagraph(Paragraph $paragraph): static
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs->add($paragraph);
            $paragraph->setMovie($this);
        }

        return $this;
    }

    public function addRecommendation(Recommendation $recommendation): static
    {
        if (!$this->recommendations->contains($recommendation)) {
            $this->recommendations->add($recommendation);
            $recommendation->setRefmovie($this);
        }

        return $this;
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
     * @return Collection<int, MovieCategory>
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
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
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

    public function getDuration(): ?int
    {
        return $this->duration;
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

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function getPosterFile(): ?File
    {
        return $this->posterFile;
    }

    /**
     * @return Collection<int, Recommendation>
     */
    public function getRecommendations(): Collection
    {
        return $this->recommendations;
    }

    public function getReleaseDate(): ?DateTime
    {
        return $this->releaseDate;
    }

    public function getSaga(): ?Saga
    {
        return $this->saga;
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

    public function removeCategory(MovieCategory $movieCategory): static
    {
        if ($this->categories->removeElement($movieCategory)) {
            $movieCategory->removeMovie($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            $company->removeMovie($this);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getPage() === $this
        ) {
            $paragraph->setMovie(null);
        }

        return $this;
    }

    public function removeRecommendation(Recommendation $recommendation): static
    {
        // set the owning side to null (unless already changed)
        if ($this->recommendations->removeElement($recommendation) && $recommendation->getRefmovie() === $this) {
            $recommendation->setRefmovie(null);
        }

        return $this;
    }

    public function setAdult(bool $adult): static
    {
        $this->adult = $adult;

        return $this;
    }

    public function setBackdrop(?string $backdrop): void
    {
        $this->backdrop = $backdrop;

        if (null === $backdrop) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setBackdropFile(?File $backdropFile = null): void
    {
        $this->backdropFile = $backdropFile;

        if ($backdropFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
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

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

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

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

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

    public function setReleaseDate(?DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function setSaga(?Saga $saga): static
    {
        $this->saga = $saga;

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
