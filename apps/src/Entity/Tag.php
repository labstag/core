<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\TagRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Table(name: 'tag')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Index(name: 'IDX_TAG_TYPE_SLUG', columns: ['slug'])]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(
    [
        'page'  => PageTag::class,
        'post'  => PostTag::class,
        'story' => StoryTag::class,
    ]
)]
abstract class Tag implements Stringable
{
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[Gedmo\Slug(updatable: true, fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getTitle();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
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
}
