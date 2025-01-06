<?php

namespace Labstag\Interface;

use Labstag\Entity\Block;
use Symfony\Component\HttpFoundation\Response;

interface BlockInterface
{
    public function content(string $view, Block $block): ?Response;

    public function generate(Block $block, array $data, bool $disable): void;
}
