<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadSeasonParagraph as EntityHeadSeasonParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Season;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadSeasonParagraph extends ParagraphAbstract implements ParagraphInterface
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

        $this->setData(
            $paragraph,
            [
                'season'    => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityHeadSeasonParagraph::class;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Head season');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-season';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return $object instanceof Block;
    }
}
