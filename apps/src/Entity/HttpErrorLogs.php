<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\HttpErrorLogsRepository;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
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
    private ?string $http_cde = null;

    #[ORM\Column(length: 255)]
    private ?string $ip = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $referer = null;

    #[ORM\ManyToOne(inversedBy: 'httpErrorLogs')]
    private ?User $refUser = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $request_data = [];

    #[ORM\Column(length: 255)]
    private ?string $request_method = null;

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

    public function getHttpCde(): ?string
    {
        return $this->http_cde;
    }

    public function setHttpCde(string $http_cde): static
    {
        $this->http_cde = $http_cde;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(string $referer): static
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
        return $this->request_data;
    }

    public function setRequestData(array $request_data): static
    {
        $this->request_data = $request_data;

        return $this;
    }

    public function getRequestMethod(): ?string
    {
        return $this->request_method;
    }

    public function setRequestMethod(string $request_method): static
    {
        $this->request_method = $request_method;

        return $this;
    }
}
