<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadPersonParagraph as EntityHeadPersonParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Person;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadPersonParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Person) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'person'    => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityHeadPersonParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head person');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-person';
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
