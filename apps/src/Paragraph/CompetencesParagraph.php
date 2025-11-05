<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\CompetencesParagraph as EntityCompetencesParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Paragraph\CompetencesType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class CompetencesParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityCompetencesParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        $competences  = $paragraph->getCompetences();
        if (!is_array($competences) || [] === $competences) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        $this->setData(
            $paragraph,
            [
                'competences' => $competences,
                'paragraph'   => $paragraph,
                'data'        => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityCompetencesParagraph::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($pageName, $paragraph);
        yield TextField::new('title', new TranslatableMessage('Title'));
        yield FormField::addColumn(12);
        $collectionField = CollectionField::new('competences', new TranslatableMessage('Competences'));
        $collectionField->setEntryToStringMethod(
            function ($link): TranslatableMessage {
                unset($link);

                return new TranslatableMessage('Competence');
            }
        );
        $collectionField->setEntryType(CompetencesType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Compétences';
    }

    #[Override]
    public function getType(): string
    {
        return 'competences';
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
