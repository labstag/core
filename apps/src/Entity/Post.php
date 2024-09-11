<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\PostRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post extends Content
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'post')]
    private Collection $meta;

    public function __construct()
    {
        $this->meta = new ArrayCollection();
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
            $metum->setPost($this);
        }

        return $this;
    }

    public function removeMetum(Meta $metum): static
    {
        if ($this->meta->removeElement($metum)) {
            // set the owning side to null (unless already changed)
            if ($metum->getPost() === $this) {
                $metum->setPost(null);
            }
        }

        return $this;
    }
}
