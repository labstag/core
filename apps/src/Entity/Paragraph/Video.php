<?php

namespace Labstag\Entity\Paragraph;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Paragraph;
use Labstag\Repository\Paragraph\VideoRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: 'paragraph_video')]
class Video
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToOne(mappedBy: 'video', cascade: ['persist', 'remove'])]
    private ?Paragraph $paragraph = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParagraph(): ?Paragraph
    {
        return $this->paragraph;
    }

    public function setParagraph(?Paragraph $paragraph): static
    {
        // unset the owning side of the relation if necessary
        if (!$paragraph instanceof Paragraph && $this->paragraph instanceof Paragraph) {
            $this->paragraph->setVideo(null);
        }

        // set the owning side of the relation if necessary
        if ($paragraph instanceof Paragraph && $paragraph->getVideo() !== $this) {
            $paragraph->setVideo($this);
        }

        $this->paragraph = $paragraph;

        return $this;
    }
}
