<?php

declare(strict_types=1);

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ContentBlock extends Block implements EntityWithParagraphsInterface
{
}
