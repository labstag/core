<?php

namespace Labstag\Twig\Runtime;

use Labstag\Entity\Block;
use Labstag\Service\BlockService;
use Twig\Extension\RuntimeExtensionInterface;

class BlockExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected BlockService $blockService
    )
    {
        // Inject dependencies if needed
    }

    public function getClass(Block $block): string
    {
        return 'block_'.$block->getType();
    }

    public function getId(Block $block): string
    {
        return 'block_'.$block->getType().'-'.$block->getId();
    }

    public function getName(string $code): string
    {
        return $this->blockService->getNameByCode($code);
    }

    public function getShow(array $tab): null|string|false
    {
        if (!isset($tab['templates']['view'])) {
            return null;
        }

        $content = $this->blockService->content(
            $tab['templates']['view'],
            $tab['block']
        );

        if (is_null($content)) {
            return null;
        }

        return $content->getContent();
    }
}
