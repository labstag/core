<?php

namespace Labstag\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Labstag\Repository\ConfigurationRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
#[Vich\Uploadable]
class Configuration
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title_format = null;

    #[ORM\Column(length: 255)]
    private ?string $site_name = null;

    #[ORM\Column]
    private bool $user_show = false;

    #[ORM\Column]
    private bool $user_link = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $placeholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'placeholder')]
    private ?File $placeholderFile = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getPlaceholderFile(): ?File
    {
        return $this->placeholderFile;
    }

    public function setPlaceholder(?string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function setPlaceholderFile(?File $placeholderFile = null): void
    {
        $this->placeholderFile = $placeholderFile;

        if ($placeholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'logo')]
    private ?File $logoFile = null;

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    public function setLogoFile(?File $logoFile = null): void
    {
        $this->logoFile = $logoFile;

        if ($logoFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $favicon = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'favicon')]
    private ?File $faviconFile = null;

    public function getFavicon(): ?string
    {
        return $this->favicon;
    }

    public function getFaviconFile(): ?File
    {
        return $this->faviconFile;
    }

    public function setFavicon(?string $favicon): void
    {
        $this->favicon = $favicon;
    }

    public function setFaviconFile(?File $faviconFile = null): void
    {
        $this->faviconFile = $faviconFile;

        if ($faviconFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getTitleFormat(): ?string
    {
        return $this->title_format;
    }

    public function setTitleFormat(string $title_format): static
    {
        $this->title_format = $title_format;

        return $this;
    }

    public function getSiteName(): ?string
    {
        return $this->site_name;
    }

    public function setSiteName(string $site_name): static
    {
        $this->site_name = $site_name;

        return $this;
    }

    public function isUserShow(): ?bool
    {
        return $this->user_show;
    }

    public function setUserShow(bool $user_show): static
    {
        $this->user_show = $user_show;

        return $this;
    }

    public function isUserLink(): ?bool
    {
        return $this->user_link;
    }

    public function setUserLink(bool $user_link): static
    {
        $this->user_link = $user_link;

        return $this;
    }
}
