<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Override;

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
        return 'Image';
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

        return in_array(
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
    }
}
