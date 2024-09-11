<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\HistoryRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History extends Content
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'history')]
    private Collection $meta;

    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'refhistory', orphanRemoval: true)]
    private Collection $chapters;

    public function __construct()
    {
        parent::__construct();
        $this->meta = new ArrayCollection();
        $this->chapters = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Meta>
     */
    public function getMeta(): Collection
    {
        return $this->meta;
    }

    public function addMetum(Meta $metum): static
    {
        if (!$this->meta->contains($metum)) {
            $this->meta->add($metum);
            $metum->setHistory($this);
        }

        return $this;
    }

    public function removeMetum(Meta $metum): static
    {
        if ($this->meta->removeElement($metum)) {
            // set the owning side to null (unless already changed)
            if ($metum->getHistory() === $this) {
                $metum->setHistory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setRefhistory($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getRefhistory() === $this) {
                $chapter->setRefhistory(null);
            }
        }

        return $this;
    }
}
