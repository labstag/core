<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MovieInfoParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (is_null($paragraph->getRefmovie())) {
            $this->setShow($paragraph, false);

            return;
        }

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
        $associationField = AssociationField::new('refmovie', new TranslatableMessage('Movie'));
        $associationField->autocomplete();
        $associationField->setSortProperty('title');

        yield $associationField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Movie Info';
    }

    #[Override]
    public function getType(): string
    {
        return 'movie_info';
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
