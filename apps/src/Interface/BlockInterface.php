<?php

namespace Labstag\Interface;

use Labstag\Entity\Block;
use Symfony\Component\HttpFoundation\Response;

interface BlockInterface
{

    public function getName(): string;

    public function getType(): string;

    public function content(string $view, Block $block): ?Response;

    public function generate(Block $block, array $data, bool $disable): void;
}
