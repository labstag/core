<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\PageRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page extends Content
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'page')]
    private Collection $meta;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $page = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'page')]
    private Collection $children;

    #[Gedmo\Slug(fields: ['title'])]
    #[Gedmo\SlugHandler(
        class: TreeSlugHandler::class,
        options: [
            'parentRelationField' => 'page',
            'separator'           => '/',
        ]
    )]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected ?string $slug = null;

    public function __construct()
    {
        parent::__construct();
        $this->meta = new ArrayCollection();
        $this->children = new ArrayCollection();
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
            $metum->setPage($this);
        }

        return $this;
    }

    public function removeMetum(Meta $metum): static
    {
        if ($this->meta->removeElement($metum)) {
            // set the owning side to null (unless already changed)
            if ($metum->getPage() === $this) {
                $metum->setPage(null);
            }
        }

        return $this;
    }

    public function getPage(): ?self
    {
        return $this->page;
    }

    public function setPage(?self $page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setPage($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getPage() === $this) {
                $child->setPage(null);
            }
        }

        return $this;
    }
}
