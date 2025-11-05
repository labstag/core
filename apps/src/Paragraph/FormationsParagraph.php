<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\FormationsParagraph as EntityFormationsParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Paragraph\FormationType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class FormationsParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityFormationsParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        $formations  = $paragraph->getFormations();
        if (0 == count($formations)) {
            $this->setShow($paragraph, false);

            return;
        }

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
        return EntityFormationsParagraph::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($pageName, $paragraph);
        yield TextField::new('title', new TranslatableMessage('Title'));
        yield FormField::addColumn(12);
        $collectionField = CollectionField::new('formations', new TranslatableMessage('Formations'));
        $collectionField->setEntryToStringMethod(
            function ($link): \Symfony\Component\Translation\TranslatableMessage {
                unset($link);

                return new TranslatableMessage('Formation');
            }
        );
        $collectionField->setEntryType(FormationType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Formations');
    }

    #[Override]
    public function getType(): string
    {
        return 'formations';
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
