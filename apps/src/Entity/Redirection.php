<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\RedirectionRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: RedirectionRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class Redirection
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Column]
    private ?int $actionCode = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $actionType = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $destination = null;

    #[ORM\Column]
    private ?bool $enable = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column]
    private ?int $lastCount = 0;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private ?bool $regex = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $source = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function getActionCode(): ?int
    {
        return $this->actionCode;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLastCount(): ?int
    {
        return $this->lastCount;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function incrementLastCount(): self
    {
        ++$this->lastCount;

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function isRegex(): ?bool
    {
        return $this->regex;
    }

    public function setActionCode(int $actionCode): static
    {
        $this->actionCode = $actionCode;

        return $this;
    }

    public function setActionType(string $actionType): static
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setLastCount(int $lastCount): static
    {
        $this->lastCount = $lastCount;

        return $this;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setRegex(bool $regex): static
    {
        $this->regex = $regex;

        return $this;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
