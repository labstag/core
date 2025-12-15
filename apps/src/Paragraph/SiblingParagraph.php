<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\SiblingParagraph as EntitySiblingParagraph;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SiblingParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $page     = $paragraph->getPage();
        $children = $page->getChildren();

        $enables = [];
        foreach ($children as $child) {
            if ($child->isEnable()) {
                $enables[] = $child;
            }
        }

        if ([] === $enables) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'childs'    => $enables,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntitySiblingParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        $wysiwgTranslation = new TranslatableMessage('Description');
        $wysiwygField = WysiwygField::new('content', $wysiwgTranslation->getMessage());
        yield $wysiwygField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Sibling pages');
    }

    #[Override]
    public function getType(): string
    {
        return 'sibling';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return Page::class == $object::class;
    }
}
