<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\HttpErrorLogsRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: HttpErrorLogsRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class HttpErrorLogs
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Column(length: 255)]
    protected ?string $agent = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $domain = null;

    #[ORM\Column(length: 255)]
    protected ?string $httpCode = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $internetProtocol = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $referer = null;

    #[ORM\ManyToOne(inversedBy: 'httpErrorLogs')]
    protected ?User $refuser = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    protected array $requestData = [];

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $requestMethod = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $url = null;

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getHttpCode(): ?string
    {
        return $this->httpCode;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getInternetProtocol(): ?string
    {
        return $this->internetProtocol;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setAgent(string $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function setHttpCode(string $httpCode): static
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    public function setInternetProtocol(string $internetProtocol): static
    {
        $this->internetProtocol = $internetProtocol;

        return $this;
    }

    public function setReferer(?string $referer): static
    {
        $this->referer = $referer;

        return $this;
    }

    public function setRefuser(?User $user): static
    {
        $this->refuser = $user;

        return $this;
    }

    /**
     * @param array<string, mixed> $requestData
     */
    public function setRequestData(array $requestData): static
    {
        $this->requestData = $requestData;

        return $this;
    }

    public function setRequestMethod(?string $requestMethod): static
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
