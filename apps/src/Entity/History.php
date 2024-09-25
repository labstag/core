<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\HistoryRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class History extends Content
{
    use SoftDeleteableEntity;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'histories')]
    private Collection $categories;

    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'refhistory', orphanRemoval: true)]
    private Collection $chapters;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Meta::class, mappedBy: 'history')]
    private Collection $meta;

    #[ORM\ManyToOne(inversedBy: 'histories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $refuser = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'histories')]
    private Collection $tags;

    public function __construct()
    {
        $this->meta       = new ArrayCollection();
        $this->chapters   = new ArrayCollection();
        $this->tags       = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addHistory($this);
        }

        return $this;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setRefhistory($this);
        }

        return $this;
    }

    public function addMetum(Meta $meta): static
    {
        if (!$this->meta->contains($meta)) {
            $this->meta->add($meta);
            $meta->setHistory($this);
        }

        return $this;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addHistory($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
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

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeHistory($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        // set the owning side to null (unless already changed)
        if ($this->chapters->removeElement($chapter) && $chapter->getRefhistory() === $this) {
            $chapter->setRefhistory(null);
        }

        return $this;
    }

    public function removeMetum(Meta $meta): static
    {
        // set the owning side to null (unless already changed)
        if ($this->meta->removeElement($meta) && $meta->getHistory() === $this) {
            $meta->setHistory(null);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeHistory($this);
        }

        return $this;
    }

    public function setRefuser(?User $user): static
    {
        $this->refuser = $user;

        return $this;
    }
}
