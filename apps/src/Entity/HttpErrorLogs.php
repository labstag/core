<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Traits\Entity\TimestampableTrait;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: HttpErrorLogsRepository::class)]
class HttpErrorLogs
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $agent = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $domain = null;

    #[ORM\Column(length: 255)]
    private ?string $httpCode = null;

    #[ORM\Column(length: 255)]
    private ?string $internetProtocol = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $referer = null;

    #[ORM\ManyToOne(inversedBy: 'httpErrorLogs')]
    private ?User $refUser = null;

    #[ORM\Column(type: Types::JSON)]
    private array $requestData = [];

    #[ORM\Column(length: 255)]
    private ?string $requestMethod = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setAgent(string $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getHttpCode(): ?string
    {
        return $this->httpCode;
    }

    public function setHttpCode(string $httpCode): static
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    public function getInternetProtocol(): ?string
    {
        return $this->internetProtocol;
    }

    public function setInternetProtocol(string $internetProtocol): static
    {
        $this->internetProtocol = $internetProtocol;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): static
    {
        $this->referer = $referer;

        return $this;
    }

    public function getRefUser(): ?User
    {
        return $this->refUser;
    }

    public function setRefUser(?User $refUser): static
    {
        $this->refUser = $refUser;

        return $this;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function setRequestData(array $requestData): static
    {
        $this->requestData = $requestData;

        return $this;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(string $requestMethod): static
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
