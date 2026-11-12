<?php

declare(strict_types=1);

namespace Labstag\Entity;

use SortDirection;

use Doctrine\Common\Collections\Collection;

interface EntityWithParagraphsInterface
{
    public function addParagraph(Paragraph $paragraph): static;

    public function getParagraphs(): Collection;

    public function removeParagraph(Paragraph $paragraph): static;
}
