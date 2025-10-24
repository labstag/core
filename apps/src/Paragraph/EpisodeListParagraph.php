<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Episode;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Season;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class EpisodeListParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Season) {
            $this->setShow($paragraph, false);

            return;
        }

        $serviceEntityRepositoryLib = $this->getRepository(Episode::class);
        $episodes                   = $serviceEntityRepositoryLib->getAllActivateBySeason($data['entity']);
        if (0 === count($episodes)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'episodes'  => $episodes,
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
        return 'Episode list';
    }

    #[Override]
    public function getType(): string
    {
        return 'episode-list';
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
