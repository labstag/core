<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\HtmlBlock as EntityHtmlBlock;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HtmlBlock extends SimpleBlockAbstract
{
    public function getClass(): string
    {
        return EntityHtmlBlock::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Block $block, string $pageName): mixed
    {
        unset($block, $pageName);
        $translatableMessage = new TranslatableMessage('Content');
        $wysiwygField        = WysiwygField::new('content', $translatableMessage->getMessage());

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('HTML');
    }

    #[Override]
    public function getType(): string
    {
        return 'html';
    }
}
