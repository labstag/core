<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

interface BlockInterface
{
    public function content(string $view, Block $block): ?Response;

    /**
     * @param mixed[] $data
     */
    public function generate(Block $block, array $data, bool $disable): void;

    public function getClass(): string;

    public function getName(): TranslatableMessage;

    public function getType(): string;

    public function isEnable(): bool;
}
