<?php

namespace Labstag\Block;

use Labstag\Entity\AdminBlock as EntityAdminBlock;
use Labstag\Entity\Block;
use Override;
use Symfony\Component\HttpFoundation\Response;

class AdminBlock extends BlockAbstract
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
                'entity' => $data['entity'],
                'block'  => $block,
                'data'   => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityAdminBlock::class;
    }

    #[Override]
    public function getName(): string
    {
        return 'Admin';
    }

    #[Override]
    public function getType(): string
    {
        return 'admin';
    }
}
