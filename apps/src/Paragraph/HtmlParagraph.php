<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HtmlParagraph extends ParagraphLib
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
        unset($paragraph, $pageName);

        yield TextField::new('title', new TranslatableMessage('Title'));
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

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
