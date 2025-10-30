<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Override;

class HeadSagaParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Saga) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'saga'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getName(): string
    {
        return 'Head saga';
    }

    #[Override]
    public function getType(): string
    {
        return 'head-saga';
    }

    #[\Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $serviceEntityRepositoryAbstract = $this->getRepository(Paragraph::class);
        $paragraph  = $serviceEntityRepositoryAbstract->findOneBy(
            [
                'type' => $this->getType(),
            ]
        );

        if (!$paragraph instanceof Paragraph) {
            return $object instanceof Block;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
