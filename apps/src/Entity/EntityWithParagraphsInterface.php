<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\Collection;

interface EntityWithParagraphsInterface
{
    public function addParagraph(Paragraph $paragraph): static;

    public function getParagraphs(): Collection;

    public function removeParagraph(Paragraph $paragraph): static;
}
