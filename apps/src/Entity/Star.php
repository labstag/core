<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\StarRepository;
use Labstag\Traits\Entity\TimestampableTrait;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: StarRepository::class)]
class Star
{
    use TimestampableTrait;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    private ?bool $enable = null;

    #[ORM\Column]
    private ?int $forks = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $license = null;

    #[ORM\Column(length: 255)]
    private ?string $repository = null;

    #[ORM\Column]
    private ?int $stargazers = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column]
    private ?int $watchers = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getForks(): ?int
    {
        return $this->forks;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function getRepository(): ?string
    {
        return $this->repository;
    }

    public function getStargazers(): ?int
    {
        return $this->stargazers;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getWatchers(): ?int
    {
        return $this->watchers;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setForks(int $forks): static
    {
        $this->forks = $forks;

        return $this;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function setLicense(?string $license): static
    {
        $this->license = $license;

        return $this;
    }

    public function setRepository(string $repository): static
    {
        $this->repository = $repository;

        return $this;
    }

    public function setStargazers(int $stargazers): static
    {
        $this->stargazers = $stargazers;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function setWatchers(int $watchers): static
    {
        $this->watchers = $watchers;

        return $this;
    }
}
