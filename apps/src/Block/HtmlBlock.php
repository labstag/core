<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Field\WysiwygField;
use Labstag\Lib\BlockLib;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class HtmlBlock extends BlockLib
{
    #[\Override]
    public function content(string $view, Block $block): ?Response
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render($view, $this->getData($block));
    }

    #[\Override]
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

    #[\Override]
    public function getFields(Block $block, string $pageName): iterable
    {
        unset($block, $pageName);
        $wysiwygField = WysiwygField::new('content', new TranslatableMessage('Content'));

        yield $wysiwygField;
    }

    #[\Override]
    public function getName(): string
    {
        return 'HTML';
    }

    #[\Override

    ]
    public function getType(): string
    {
        return 'html';
    }
}
