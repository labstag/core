<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Entity\Traits\WorkflowTrait;
use Labstag\Repository\UserRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;
    use WorkflowTrait;

    /**
     * @var int
     */
    protected const DATAUNSERIALIZE = 3;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $avatar = null;

    #[Vich\UploadableField(mapping: 'avatar', fileNameProperty: 'avatar')]
    protected ?File $avatarFile = null;

    /**
     * @var Collection<int, Edito>
     */
    #[ORM\OneToMany(targetEntity: Edito::class, mappedBy: 'refuser', cascade: ['persist', 'detach'])]
    protected Collection $editos;

    #[ORM\Column(length: 180, unique: true)]
    protected ?string $email = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: [
            'default' => 1,
        ]
    )]
    protected ?bool $enable = null;

    /**
     * @var Collection<int, HttpErrorLogs>
     */
    #[ORM\OneToMany(targetEntity: HttpErrorLogs::class, mappedBy: 'refuser')]
    protected Collection $httpErrorLogs;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(
        length: 2,
        options: [
            'default' => 'fr',
        ]
    )]
    protected ?string $language = null;

    /**
     * @var Collection<int, Memo>
     */
    #[ORM\OneToMany(targetEntity: Memo::class, mappedBy: 'refuser', cascade: ['persist', 'detach'])]
    protected Collection $memos;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'refuser', cascade: ['persist', 'detach'])]
    protected Collection $pages;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    protected ?string $password = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'refuser', cascade: ['persist', 'detach'])]
    protected Collection $posts;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    protected array $roles = ['ROLE_USER'];

    /**
     * @var Collection<int, Story>
     */
    #[ORM\OneToMany(targetEntity: Story::class, mappedBy: 'refuser', cascade: ['persist', 'detach'])]
    protected Collection $stories;

    #[ORM\Column(length: 255, unique: true)]
    protected ?string $username = null;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'users')]
    private Collection $groups;

    public function __construct()
    {
        $this->stories       = new ArrayCollection();
        $this->editos        = new ArrayCollection();
        $this->memos         = new ArrayCollection();
        $this->pages         = new ArrayCollection();
        $this->posts         = new ArrayCollection();
        $this->httpErrorLogs = new ArrayCollection();
        $this->groups        = new ArrayCollection();
    }

    /**
     * @return mixed[]
     */
    public function __serialize(): array
    {
        return [$this->id, $this->username, $this->email];
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    /**
     * @param array<string, mixed> $data
     */
    /**
     * @param array{0: mixed, 1: mixed, 2: mixed} $data
     */
    public function __unserialize(array $data): void
    {
        if (self::DATAUNSERIALIZE === count($data)) {
            [
                $this->id,
                $this->username,
                $this->email,
            ] = $data;
        }
    }

    public function addEdito(Edito $edito): static
    {
        if (!$this->editos->contains($edito)) {
            $this->editos->add($edito);
            $edito->setRefuser($this);
        }

        return $this;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addUser($this);
        }

        return $this;
    }

    public function addHttpErrorLog(HttpErrorLogs $httpErrorLogs): static
    {
        if (!$this->httpErrorLogs->contains($httpErrorLogs)) {
            $this->httpErrorLogs->add($httpErrorLogs);
            $httpErrorLogs->setRefUser($this);
        }

        return $this;
    }

    public function addMemo(Memo $memo): static
    {
        if (!$this->memos->contains($memo)) {
            $this->memos->add($memo);
            $memo->setRefuser($this);
        }

        return $this;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setRefuser($this);
        }

        return $this;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setRefuser($this);
        }

        return $this;
    }

    public function addStory(Story $story): static
    {
        if (!$this->stories->contains($story)) {
            $this->stories->add($story);
            $story->setRefuser($this);
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[Override]
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    /**
     * @return Collection<int, Edito>
     */
    public function getEditos(): Collection
    {
        return $this->editos;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @return Collection<int, HttpErrorLogs>
     */
    public function getHttpErrorLogs(): Collection
    {
        return $this->httpErrorLogs;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
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
     * @see PasswordAuthenticatedUserInterface
     */
    #[Override]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    #[Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @return Collection<int, Story>
     */
    public function getStories(): Collection
    {
        return $this->stories;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[Override]
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function removeEdito(Edito $edito): static
    {
        // set the owning side to null (unless already changed)
        if ($this->editos->removeElement($edito) && $edito->getRefuser() === $this
        ) {
            $edito->setRefuser(null);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            $group->removeUser($this);
        }

        return $this;
    }

    public function removeHttpErrorLog(HttpErrorLogs $httpErrorLogs): static
    {
        // set the owning side to null (unless already changed)
        if ($this->httpErrorLogs->removeElement($httpErrorLogs) && $httpErrorLogs->getRefUser() === $this
        ) {
            $httpErrorLogs->setRefUser(null);
        }

        return $this;
    }

    public function removeMemo(Memo $memo): static
    {
        // set the owning side to null (unless already changed)
        if ($this->memos->removeElement($memo) && $memo->getRefuser() === $this
        ) {
            $memo->setRefuser(null);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        // set the owning side to null (unless already changed)
        if ($this->pages->removeElement($page) && $page->getRefuser() === $this
        ) {
            $page->setRefuser(null);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        // set the owning side to null (unless already changed)
        if ($this->posts->removeElement($post) && $post->getRefuser() === $this
        ) {
            $post->setRefuser(null);
        }

        return $this;
    }

    public function removeStory(Story $story): static
    {
        // set the owning side to null (unless already changed)
        if ($this->stories->removeElement($story) && $story->getRefuser() === $this
        ) {
            $story->setRefuser(null);
        }

        return $this;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;

        if (null === $avatar) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setAvatarFile(?File $avatarFile = null): void
    {
        $this->avatarFile = $avatarFile;

        if ($avatarFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }
}
