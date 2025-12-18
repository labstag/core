<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\CastingRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: CastingRepository::class)]
class Casting
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $figure = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $knownForDepartment = null;

    #[ORM\ManyToOne(inversedBy: 'castings')]
    private ?Episode $refEpisode = null;

    #[ORM\ManyToOne(inversedBy: 'castings')]
    private ?Movie $refMovie = null;

    #[ORM\ManyToOne(inversedBy: 'castings')]
    private ?Person $refPerson = null;

    #[ORM\ManyToOne(inversedBy: 'castings')]
    private ?Season $refSeason = null;

    #[ORM\ManyToOne(inversedBy: 'castings')]
    private ?Serie $refSerie = null;

    public function getFigure(): ?string
    {
        return $this->figure;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKnownForDepartment(): ?string
    {
        return $this->knownForDepartment;
    }

    public function getRefEpisode(): ?Episode
    {
        return $this->refEpisode;
    }

    public function getRefMovie(): ?Movie
    {
        return $this->refMovie;
    }

    public function getRefPerson(): ?Person
    {
        return $this->refPerson;
    }

    public function getRefSeason(): ?Season
    {
        return $this->refSeason;
    }

    public function getRefSerie(): ?Serie
    {
        return $this->refSerie;
    }

    public function setFigure(?string $figure): static
    {
        $this->figure = $figure;

        return $this;
    }

    public function setKnownForDepartment(?string $knownForDepartment): static
    {
        $this->knownForDepartment = $knownForDepartment;

        return $this;
    }

    public function setRefEpisode(?Episode $refEpisode): static
    {
        $this->refEpisode = $refEpisode;

        return $this;
    }

    public function setRefMovie(?Movie $refMovie): static
    {
        $this->refMovie = $refMovie;

        return $this;
    }

    public function setRefPerson(?Person $refPerson): static
    {
        $this->refPerson = $refPerson;

        return $this;
    }

    public function setRefSeason(?Season $refSeason): static
    {
        $this->refSeason = $refSeason;

        return $this;
    }

    public function setRefSerie(?Serie $refSerie): static
    {
        $this->refSerie = $refSerie;

        return $this;
    }
}
