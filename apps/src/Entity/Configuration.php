<?php

namespace Labstag\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Labstag\Repository\ConfigurationRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
#[Vich\Uploadable]
class Configuration
{
    use TimestampableEntity;

    #[ORM\Column(length: 255)]
    private ?string $copyright = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $favicon = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'favicon')]
    private ?File $faviconFile = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'logo')]
    private ?File $logoFile = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $noreply = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $placeholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'placeholder')]
    private ?File $placeholderFile = null;

    #[ORM\Column(length: 255)]
    private ?string $titleFormat = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column]
    private bool $userLink = false;

    #[ORM\Column]
    private bool $userShow = false;

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFavicon(): ?string
    {
        return $this->favicon;
    }

    public function getFaviconFile(): ?File
    {
        return $this->faviconFile;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNoreply(): ?string
    {
        return $this->noreply;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getPlaceholderFile(): ?File
    {
        return $this->placeholderFile;
    }

    public function getTitleFormat(): ?string
    {
        return $this->titleFormat;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isUserLink(): ?bool
    {
        return $this->userLink;
    }

    public function isUserShow(): ?bool
    {
        return $this->userShow;
    }

    public function setCopyright(string $copyright): static
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setNoreply(string $noreply): static
    {
        $this->noreply = $noreply;

        return $this;
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

    public function setTitleFormat(string $titleFormat): static
    {
        $this->titleFormat = $titleFormat;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function setUserLink(bool $userLink): static
    {
        $this->userLink = $userLink;

        return $this;
    }

    public function setUserShow(bool $userShow): static
    {
        $this->userShow = $userShow;

        return $this;
    }
}
