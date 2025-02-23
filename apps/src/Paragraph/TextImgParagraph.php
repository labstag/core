<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Labstag\Lib\ParagraphLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TextImgParagraph extends ParagraphLib
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
        yield BooleanField::new('leftposition', new TranslatableMessage('Image on the left'));
        $wysiwygField = WysiwygField::new('content', 'Texte');

        yield $wysiwygField;
    }

    public function getClasses(Paragraph $paragraph): array
    {
        $tab = parent::getClasses($paragraph);
        if ($paragraph->isLeftposition()) {
            $tab[] = 'text-img-left';
        }

        return $tab;
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
        return $this->useInAll();
    }
}
