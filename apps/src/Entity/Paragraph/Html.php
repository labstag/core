<?php

namespace Labstag\Entity\Paragraph;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Paragraph;
use Labstag\Interface\ParagraphInterface;
use Labstag\Repository\Paragraph\HtmlRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: HtmlRepository::class)]
#[ORM\Table(name: 'paragraph_html')]
class Html implements ParagraphInterface
{

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToOne(mappedBy: 'html', cascade: ['persist', 'remove'])]
    private ?Paragraph $paragraph = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParagraph(): ?Paragraph
    {
        return $this->paragraph;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setParagraph(?Paragraph $paragraph): static
    {
        // unset the owning side of the relation if necessary
        if (!$paragraph instanceof Paragraph && $this->paragraph instanceof Paragraph) {
            $this->paragraph->setHtml(null);
        }

        // set the owning side of the relation if necessary
        if ($paragraph instanceof Paragraph && $paragraph->getHtml() !== $this) {
            $paragraph->setHtml($this);
        }

        $this->paragraph = $paragraph;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
