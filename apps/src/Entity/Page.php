<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\PageRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Page extends Content
{
    use SoftDeleteableEntity;

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

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'page')]
    private Collection $children;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'page')]
    private Collection $meta;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $page = null;

    public function __construct()
    {
        $this->meta     = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setPage($this);
        }

        return $this;
    }

    public function addMetum(Meta $meta): static
    {
        if (!$this->meta->contains($meta)) {
            $this->meta->add($meta);
            $meta->setPage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
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

    public function getPage(): ?self
    {
        return $this->page;
    }

    public function removeChild(self $child): static
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($child) && $child->getPage() === $this) {
            $child->setPage(null);
        }

        return $this;
    }

    public function removeMetum(Meta $meta): static
    {
        // set the owning side to null (unless already changed)
        if ($this->meta->removeElement($meta) && $meta->getPage() === $this) {
            $meta->setPage(null);
        }

        return $this;
    }

    public function setPage(?self $page): static
    {
        $this->page = $page;

        return $this;
    }
}
