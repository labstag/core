<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\MediaRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[Vich\Uploadable]
class Media
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[Vich\UploadableField(mapping: 'media', fileNameProperty: 'name', size: 'size', mimeType: 'mimeType')]
    protected ?File $file = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $mimeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(nullable: true)]
    protected ?int $size = null;

    #[Gedmo\Slug(fields: ['name'], updatable: true)]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    protected ?string $slug = null;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;

        if ($file instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;

        if (null === $name) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
