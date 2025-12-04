<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FormParagraph extends Paragraph
{
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $form = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: [
            'default' => 1,
        ]
    )]
    protected bool $save = true;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getForm(): ?string
    {
        return $this->form;
    }

    public function isSave(): ?bool
    {
        return $this->save;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function setForm(?string $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function setSave(bool $save): static
    {
        $this->save = $save;

        return $this;
    }
}
