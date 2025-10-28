<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SiblingParagraph extends ParagraphAbstract
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

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        $wysiwygField = WysiwygField::new('content', new TranslatableMessage('Description'));
        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Page enfante';
    }

    #[Override]
    public function getType(): string
    {
        return 'sibling';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Page::class];
    }
}
