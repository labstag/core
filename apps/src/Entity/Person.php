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
use Labstag\Repository\PersonRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Index(name: 'IDX_PERSORN_SLUG', columns: ['slug'])]
class Person
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\OneToOne(inversedBy: 'person', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Meta $meta = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $profile = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[Vich\UploadableField(mapping: 'movie', fileNameProperty: 'profile')]
    protected ?File $profileFile = null;

    #[Gedmo\Slug(fields: ['title'], updatable: true, unique: false)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $birthday = null;

    /**
     * @var Collection<int, Casting>
     */
    #[ORM\OneToMany(targetEntity: Casting::class, mappedBy: 'refPerson')]
    private Collection $castings;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $deathday = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $placeOfBirth = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tmdb = null;

    /**
     * @var Collection<int, Paragraph>
     */
    #[ORM\OneToMany(
        targetEntity: Paragraph::class,
        mappedBy: 'person',
        cascade: [
            'persist',
            'remove',
        ],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(
        ['position' => 'ASC']
    )]
    protected Collection $paragraphs;

    public function __construct()
    {
        $this->castings = new ArrayCollection();
        $this->paragraphs      = new ArrayCollection();
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
            $paragraph->setPerson($this);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): static
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphs->removeElement($paragraph) && $paragraph->getPerson() === $this
        ) {
            $paragraph->setPerson(null);
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

    public function addCasting(Casting $casting): static
    {
        if (!$this->castings->contains($casting)) {
            $this->castings->add($casting);
            $casting->setRefPerson($this);
        }

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    /**
     * @return Collection<int, Casting>
     */
    public function getCastings(): Collection
    {
        return $this->castings;
    }

    public function getDeathday(): ?DateTime
    {
        return $this->deathday;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getPlaceOfBirth(): ?string
    {
        return $this->placeOfBirth;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function getProfileFile(): ?File
    {
        return $this->profileFile;
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

    public function removeCasting(Casting $casting): static
    {
        // set the owning side to null (unless already changed)
        if ($this->castings->removeElement($casting) && $casting->getRefPerson() === $this) {
            $casting->setRefPerson(null);
        }

        return $this;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function setBirthday(?DateTime $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function setDeathday(?DateTime $deathday): static
    {
        $this->deathday = $deathday;

        return $this;
    }

    public function setGender(?int $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function setPlaceOfBirth(?string $placeOfBirth): static
    {
        $this->placeOfBirth = $placeOfBirth;

        return $this;
    }

    public function setProfile(?string $profile): void
    {
        $this->profile = $profile;

        if (null === $profile) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setProfileFile(?File $profileFile = null): void
    {
        $this->profileFile = $profileFile;

        if ($profileFile instanceof File) {
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

    public function setTmdb(?string $tmdb): static
    {
        $this->tmdb = $tmdb;

        return $this;
    }
}
