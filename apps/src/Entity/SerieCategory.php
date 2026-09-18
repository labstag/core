<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SerieCategory extends Category
{

    /**
     * @var Collection<int, Serie>
     */
    #[ORM\ManyToMany(targetEntity: Serie::class, inversedBy: 'categories', cascade: ['persist', 'detach'])]
    #[ORM\JoinTable(name: 'category_serie')]
    protected Collection $series;

    public function __construct()
    {
        parent::__construct();
        $this->series   = new ArrayCollection();
    }

    public function addSerie(Serie $serie): static
    {
        if (!$this->series->contains($serie)) {
            $this->series->add($serie);
        }

        return $this;
    }

    /**
     * @return Collection<int, Serie>
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function removeSerie(Serie $serie): static
    {
        $this->series->removeElement($serie);

        return $this;
    }
}
