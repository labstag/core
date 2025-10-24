<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SeasonListParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Serie) {
            $this->setShow($paragraph, false);

            return;
        }

        $serviceEntityRepositoryLib = $this->getRepository(Season::class);
        $seasons                    = $serviceEntityRepositoryLib->getAllActivateBySerie($data['entity']);
        if (0 === count($seasons)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'seasons'   => $seasons,
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
    }

    #[Override]
    public function getName(): string
    {
        return 'Season list';
    }

    #[Override]
    public function getType(): string
    {
        return 'season-list';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Block::class];
    }
}
