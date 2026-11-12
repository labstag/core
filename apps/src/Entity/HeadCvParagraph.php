<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class HeadCvParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $pdf = null;

    #[Vich\UploadableField(mapping: 'paragraph', fileNameProperty: 'pdf')]
    protected ?File $pdfFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function getPdfFile(): ?File
    {
        return $this->pdfFile;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setPdf(?string $pdf): void
    {
        $this->pdf = $pdf;

        if (null === $pdf) {
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setPdfFile(?File $pdfFile = null): void
    {
        $this->pdfFile = $pdfFile;

        if ($pdfFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
