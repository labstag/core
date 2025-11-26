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
use Labstag\Repository\PlatformRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PlatformRepository::class)]
#[ORM\Index(name: 'IDX_PLATFORM_SLUG', columns: ['slug'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
class Platform implements \Stringable
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

    #[Vich\UploadableField(mapping: 'platform', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $abbreviation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $family = null;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\ManyToMany(targetEntity: Game::class, mappedBy: 'platforms')]
    private Collection $games;

    #[ORM\Column]
    private ?int $generation = null;

    #[ORM\Column(length: 255)]
    private ?string $igdb = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->addPlatform($this);
        }

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function getGeneration(): ?int
    {
        return $this->generation;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            $game->removePlatform($this);
        }

        return $this;
    }

    public function setAbbreviation(string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function setFamily(?string $family): static
    {
        $this->family = $family;

        return $this;
    }

    public function setGeneration(int $generation): static
    {
        $this->generation = $generation;

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
}
