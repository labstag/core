<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageParagraph extends ParagraphLib
{
    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        $textField = TextField::new('imgFile');
        $textField->setFormType(VichImageType::class);

        yield $textField;
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
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
