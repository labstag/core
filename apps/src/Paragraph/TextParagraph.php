<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Game;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Entity\TextParagraph as EntityTextParagraph;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TextParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $this->setData(
            $paragraph,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityTextParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        $wysiwgTranslation = new TranslatableMessage('Text');
        $wysiwygField = WysiwygField::new('content', $wysiwgTranslation->getMessage());

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Text');
    }

    #[Override]
    public function getType(): string
    {
        return 'text';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $inArray = in_array(
            $object::class,
            [
                Block::class,
                Chapter::class,
                Edito::class,
                Game::class,
                Memo::class,
                Page::class,
                Post::class,
                Story::class,
            ]
        );

        return $inArray || $object instanceof Block;
    }
}
