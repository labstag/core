<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\CategoryRepository;
use Override;
use Stringable;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Category implements Stringable
{
    use SoftDeleteableEntity;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', cascade: ['persist', 'detach'])]
    private Collection $children;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\ManyToMany(targetEntity: Page::class, inversedBy: 'categories', cascade: ['persist', 'detach'])]
    private Collection $pages;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children', cascade: ['persist', 'detach'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?self $parent = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, inversedBy: 'categories', cascade: ['persist', 'detach'])]
    private Collection $posts;

    #[Gedmo\Slug(updatable: true, fields: ['title'], unique_base: 'type')]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Story>
     */
    #[ORM\ManyToMany(targetEntity: Story::class, inversedBy: 'categories', cascade: ['persist', 'detach'])]
    private Collection $stories;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->stories  = new ArrayCollection();
        $this->pages    = new ArrayCollection();
        $this->posts    = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
        }

        return $this;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        return $this;
    }

    public function addStory(Story $story): static
    {
        if (!$this->stories->contains($story)) {
            $this->stories->add($story);
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

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @return Collection<int, Story>
     */
    public function getStories(): Collection
    {
        return $this->stories;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function removeChild(self $child): static
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($child) && $child->getCategory() === $this) {
            $child->setParent(null);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        $this->pages->removeElement($page);

        return $this;
    }

    public function removePost(Post $post): static
    {
        $this->posts->removeElement($post);

        return $this;
    }

    public function removeStory(Story $story): static
    {
        $this->stories->removeElement($story);

        return $this;
    }

    public function setParent(?self $category): static
    {
        $this->parent = $category;

        return $this;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
