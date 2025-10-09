<?php

namespace Labstag\Interface;

use Labstag\Entity\Block;
use Symfony\Component\HttpFoundation\Response;

interface BlockInterface
{
    public function content(string $view, Block $block): ?Response;

    /**
     * @param mixed[] $data
     */
    public function generate(Block $block, array $data, bool $disable): void;

    public function getName(): string;

    public function getType(): string;

    public function isEnable(): bool;
}
