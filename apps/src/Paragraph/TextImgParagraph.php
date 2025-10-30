<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TextImgParagraph extends ParagraphAbstract
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

    #[Override]
    public function getClasses(Paragraph $paragraph): array
    {
        $tab = parent::getClasses($paragraph);
        if ($paragraph->isLeftposition()) {
            $tab[] = 'text-img-left';
        }

        return $tab;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph);
        yield $this->addFieldImageUpload('img', $pageName);
        yield BooleanField::new('leftposition', new TranslatableMessage('Image on the left'));
        $wysiwygField = WysiwygField::new('content', 'Texte');

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Texte image';
    }

    #[Override]
    public function getType(): string
    {
        return 'text-img';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [
            Block::class,
            Chapter::class,
            Edito::class,
            Story::class,
            Memo::class,
            Page::class,
            Post::class,
        ];
    }
}
