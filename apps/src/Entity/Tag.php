<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\TagRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Tag implements Stringable
{
    use SoftDeleteableEntity;

    /**
     * @var Collection<int, Chapter>
     */
    #[ORM\ManyToMany(targetEntity: Chapter::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_chapter')]
    private Collection $chapters;

    /**
     * @var Collection<int, Edito>
     */
    #[ORM\ManyToMany(targetEntity: Edito::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_edito')]
    private Collection $editos;

    /**
     * @var Collection<int, History>
     */
    #[ORM\ManyToMany(targetEntity: History::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_history')]
    private Collection $histories;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    /**
     * @var Collection<int, Memo>
     */
    #[ORM\ManyToMany(targetEntity: Memo::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_memo')]
    private Collection $memos;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\ManyToMany(targetEntity: Page::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_page')]
    private Collection $pages;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tag_post')]
    private Collection $posts;

    #[Gedmo\Slug(updatable: true, fields: ['title'], unique_base: 'type')]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function __construct()
    {
        $this->posts     = new ArrayCollection();
        $this->pages     = new ArrayCollection();
        $this->histories = new ArrayCollection();
        $this->editos    = new ArrayCollection();
        $this->memos     = new ArrayCollection();
        $this->chapters  = new ArrayCollection();
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
        }

        return $this;
    }

    public function addEdito(Edito $edito): static
    {
        if (!$this->editos->contains($edito)) {
            $this->editos->add($edito);
        }

        return $this;
    }

    public function addHistory(History $history): static
    {
        if (!$this->histories->contains($history)) {
            $this->histories->add($history);
        }

        return $this;
    }

    public function addMemo(Memo $memo): static
    {
        if (!$this->memos->contains($memo)) {
            $this->memos->add($memo);
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

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    /**
     * @return Collection<int, Edito>
     */
    public function getEditos(): Collection
    {
        return $this->editos;
    }

    /**
     * @return Collection<int, History>
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Memo>
     */
    public function getMemos(): Collection
    {
        return $this->memos;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function removeChapter(Chapter $chapter): static
    {
        $this->chapters->removeElement($chapter);

        return $this;
    }

    public function removeEdito(Edito $edito): static
    {
        $this->editos->removeElement($edito);

        return $this;
    }

    public function removeHistory(History $history): static
    {
        $this->histories->removeElement($history);

        return $this;
    }

    public function removeMemo(Memo $memo): static
    {
        $this->memos->removeElement($memo);

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

    public function setSlug(string $slug): static
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
