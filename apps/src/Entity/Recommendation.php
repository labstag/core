<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\RecommendationRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: RecommendationRepository::class)]
class Recommendation
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster = null;

    #[ORM\ManyToOne(inversedBy: 'recommendations')]
    private ?Movie $refmovie = null;

    #[ORM\ManyToOne(inversedBy: 'recommendations')]
    private ?Saga $refsaga = null;

    #[ORM\ManyToOne(inversedBy: 'recommendations')]
    private ?Serie $refserie = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $releaseDate = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tmdb = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function getRefmovie(): ?Movie
    {
        return $this->refmovie;
    }

    public function getRefsaga(): ?Saga
    {
        return $this->refsaga;
    }

    public function getRefserie(): ?Serie
    {
        return $this->refserie;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTmdb(): ?string
    {
        return $this->tmdb;
    }

    public function setOverview(?string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function setPoster(?string $poster): static
    {
        $this->poster = $poster;

        return $this;
    }

    public function setRefmovie(?Movie $movie): static
    {
        $this->refmovie = $movie;

        return $this;
    }

    public function setRefsaga(?Saga $saga): static
    {
        $this->refsaga = $saga;

        return $this;
    }

    public function setRefserie(?Serie $serie): static
    {
        $this->refserie = $serie;

        return $this;
    }

    public function setReleaseDate(\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

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
