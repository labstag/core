<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Block;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HtmlBlock extends SimpleBlockAbstract
{
    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Block $block, string $pageName): mixed
    {
        unset($block, $pageName);
        $wysiwygField = WysiwygField::new('content', new TranslatableMessage('Content'));

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'HTML';
    }

    #[Override]
    public function getType(): string
    {
        return 'html';
    }
}
