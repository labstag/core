<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\TextImgParagraph as EntityTextImgParagraph;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TextImgParagraph extends ParagraphAbstract implements ParagraphInterface
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
        return EntityTextImgParagraph::class;
    }

    #[Override]
    public function getClasses(Paragraph $paragraph): array
    {
        $tab = parent::getClasses($paragraph);
        if ($paragraph instanceof EntityTextImgParagraph && $paragraph->isLeftposition()) {
            $tab[] = 'text-img-left';
        }

        return $tab;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): Generator
    {
        yield $this->addFieldImageUpload('img', $pageName, $paragraph);
        yield BooleanField::new('leftposition', new TranslatableMessage('Image on the left'));
        $wysiwgTranslation = new TranslatableMessage('Text');
        $wysiwygField = WysiwygField::new('content', $wysiwgTranslation->getMessage());

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Text Image');
    }

    #[Override]
    public function getType(): string
    {
        return 'text-img';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $inArray = in_array($object::class, [Block::class, Edito::class, Memo::class, Page::class, Post::class]);

        return $inArray || $object instanceof Block;
    }
}
