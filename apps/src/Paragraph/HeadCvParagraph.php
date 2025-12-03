<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\HeadCvParagraph as EntityHeadCvParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadCvParagraph extends ParagraphAbstract implements ParagraphInterface
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

    public function getClass(): string
    {
        return EntityHeadCvParagraph::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): \Generator
    {
        yield TextField::new('title', new TranslatableMessage('Title'));
        yield $this->addFieldFileUpload('pdf', $pageName, $paragraph);
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head CV');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-cv';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $entityRepository                = $this->getRepository($this->getClass());
        $paragraph                       = $entityRepository->findOneBy([]);

        if (!$paragraph instanceof Paragraph) {
            return $object instanceof Page && $object->getType() == PageEnum::CV->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
