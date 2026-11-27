<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class TextImgParagraph extends Paragraph
{

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $img = null;

    #[Vich\UploadableField(mapping: 'paragraph', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $leftposition = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function isLeftposition(): ?bool
    {
        return $this->leftposition;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function setImg(?string $img): void
    {
        $this->img = $img;
    }

    public function setImgFile(?File $imgFile = null): void
    {
        $this->imgFile = $imgFile;
    }

    public function setLeftposition(?bool $leftposition): static
    {
        $this->leftposition = $leftposition;

        return $this;
    }
}
