<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MovieCategory extends Category
{

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\ManyToMany(targetEntity: Movie::class, inversedBy: 'categories', cascade: ['persist', 'detach'])]
    #[ORM\JoinTable(name: 'category_movie')]
    protected Collection $movies;

    public function __construct()
    {
        parent::__construct();
        $this->movies   = new ArrayCollection();
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
        }

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function removeMovie(Movie $movie): static
    {
        $this->movies->removeElement($movie);

        return $this;
    }
}
