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
use Labstag\Repository\GameRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Index(name: 'IDX_GAME_SLUG', columns: ['slug'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
class Game
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $img = null;

    #[Vich\UploadableField(mapping: 'game', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(nullable: true)]
    private ?array $artworks = null;

    /**
     * @var Collection<int, GameCategory>
     */
    #[ORM\ManyToMany(targetEntity: GameCategory::class, inversedBy: 'games')]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    private Collection $categories;

    /**
     * @var Collection<int, Franchise>
     */
    #[ORM\ManyToMany(targetEntity: Franchise::class, inversedBy: 'games')]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    private Collection $franchises;

    #[ORM\Column(length: 255)]
    private ?string $igdb = null;

    /**
     * @var Collection<int, Platform>
     */
    #[ORM\ManyToMany(targetEntity: Platform::class, inversedBy: 'games')]
    #[ORM\OrderBy(
        ['title' => 'ASC']
    )]
    private Collection $platforms;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $release_date = null;

    #[ORM\Column(nullable: true)]
    private ?array $screenshots = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?array $videos = null;

    public function __construct()
    {
        $this->franchises = new ArrayCollection();
        $this->platforms  = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function addCategory(GameCategory $gameCategory): static
    {
        if (!$this->categories->contains($gameCategory)) {
            $this->categories->add($gameCategory);
        }

        return $this;
    }

    public function addFranchise(Franchise $franchise): static
    {
        if (!$this->franchises->contains($franchise)) {
            $this->franchises->add($franchise);
        }

        return $this;
    }

    public function addPlatform(Platform $platform): static
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms->add($platform);
        }

        return $this;
    }

    public function getArtworks(): ?array
    {
        return $this->artworks;
    }

    /**
     * @return Collection<int, GameCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Collection<int, Franchise>
     */
    public function getFranchises(): Collection
    {
        return $this->franchises;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIgdb(): ?string
    {
        return $this->igdb;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    /**
     * @return Collection<int, Platform>
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function getReleaseDate(): ?DateTime
    {
        return $this->release_date;
    }

    public function getScreenshots(): ?array
    {
        return $this->screenshots;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getVideos(): ?array
    {
        return $this->videos;
    }

    public function removeCategory(GameCategory $gameCategory): static
    {
        $this->categories->removeElement($gameCategory);

        return $this;
    }

    public function removeFranchise(Franchise $franchise): static
    {
        $this->franchises->removeElement($franchise);

        return $this;
    }

    public function removePlatform(Platform $platform): static
    {
        $this->platforms->removeElement($platform);

        return $this;
    }

    public function setArtworks(?array $artworks): static
    {
        $this->artworks = $artworks;

        return $this;
    }

    public function setIgdb(string $igdb): static
    {
        $this->igdb = $igdb;

        return $this;
    }

    public function setImg(?string $img): void
    {
        $this->img = $img;

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

    public function setReleaseDate(?DateTime $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function setScreenshots(?array $screenshots): static
    {
        $this->screenshots = $screenshots;

        return $this;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function setVideos(?array $videos): static
    {
        $this->videos = $videos;

        return $this;
    }
}
