<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\EpisodeRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
#[Vich\Uploadable]
class Episode
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[Vich\UploadableField(mapping: 'episode', fileNameProperty: 'img')]
    private ?File $imgFile = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\ManyToOne(inversedBy: 'episodes')]
    private ?Season $refseason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(name: 'vote_count', nullable: true)]
    private ?int $voteCount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tmdb = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected ?bool $enable = null;

    #[ORM\Column(name: 'air_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $airDate = null;

    #[ORM\Column(name: 'vote_average', nullable: true)]
    private ?float $voteAverage = null;

    public function getTmdb(): ?string
    {
        return $this->tmdb;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(?string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function getAirDate(): ?DateTime
    {
        return $this->airDate;
    }

    public function setAirDate(?DateTime $airDate): static
    {
        $this->airDate = $airDate;

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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): void
    {
        $this->img = $img;

        // Si l'image est supprimée (img devient null), on force la mise à jour
        if (null === $img) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setTmdb(?string $tmdb): static
    {
        $this->tmdb = $tmdb;

        return $this;
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

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getRefseason(): ?Season
    {
        return $this->refseason;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getVoteAverage(): ?float
    {
        return $this->voteAverage;
    }

    public function getVoteCount(): ?int
    {
        return $this->voteCount;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function setRefseason(?Season $refseason): static
    {
        $this->refseason = $refseason;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setVoteAverage(float $voteAverage): static
    {
        $this->voteAverage = $voteAverage;

        return $this;
    }

    public function setVoteCount(int $voteCount): static
    {
        $this->voteCount = $voteCount;

        return $this;
    }
}
