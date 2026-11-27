<?php

namespace Labstag\Entity;

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
    }

    public function setPdfFile(?File $pdfFile = null): void
    {
        $this->pdfFile = $pdfFile;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
