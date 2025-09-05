<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Repository\SagaRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: SagaRepository::class)]
class Saga implements Stringable
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\OneToMany(targetEntity: Movie::class, mappedBy: 'saga')]
    private Collection $movies;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
            $movie->setSaga($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        // set the owning side to null (unless already changed)
        if ($this->movies->removeElement($movie) && $movie->getSaga() === $this) {
            $movie->setSaga(null);
        }

        return $this;
    }
}
