<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\RedirectionRepository;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Traits\Entity\TimestampableTrait;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: RedirectionRepository::class)]
class Redirection
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column]
    private ?int $action_code = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $action_type = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $data = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $destination = null;

    #[ORM\Column]
    private ?bool $enable = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $source = null;

    #[ORM\Column]
    private ?int $last_count = null;

    #[ORM\Column]
    private ?bool $regex = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getActionCode(): ?int
    {
        return $this->action_code;
    }

    public function setActionCode(int $action_code): static
    {
        $this->action_code = $action_code;

        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->action_type;
    }

    public function setActionType(string $action_type): static
    {
        $this->action_type = $action_type;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getLastCount(): ?int
    {
        return $this->last_count;
    }

    public function setLastCount(int $last_count): static
    {
        $this->last_count = $last_count;

        return $this;
    }

    public function isRegex(): ?bool
    {
        return $this->regex;
    }

    public function setRegex(bool $regex): static
    {
        $this->regex = $regex;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
