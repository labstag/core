<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;
use Symfony\Component\HttpFoundation\Response;

class FlashbagBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block): ?Response
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render($view, $this->getData($block));
    }

    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Block $block, array $data, bool $disable): void
    {
        unset($disable);
        $this->setData(
            $block,
            [
                'block' => $block,
                'data'  => $data,
            ]
        );
    }

    #[Override]
    public function getName(): string
    {
        return 'Flashbag';
    }

    #[Override]
    public function getType(): string
    {
        return 'flashbag';
    }
}
