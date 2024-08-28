<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\MemoRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: MemoRepository::class)]
class Memo extends Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }
}
