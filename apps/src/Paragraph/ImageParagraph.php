<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\ImageParagraph as EntityImageParagraph;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ImageParagraph extends ParagraphAbstract implements ParagraphInterface
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
        return EntityImageParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph);
        yield $this->addFieldImageUpload('img', $pageName);
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Image');
    }

    #[Override]
    public function getType(): string
    {
        return 'img';
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
                Story::class,
                Memo::class,
                Page::class,
                Post::class,
            ]
        );

        return $inArray || $object instanceof Block;
    }
}
