<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\ExperiencesParagraph as EntityExperiencesParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Paragraph\ExperienceType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ExperiencesParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityExperiencesParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        $skills  = $paragraph->getSkills();
        if (!is_array($skills) || [] === $skills) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        $this->setData(
            $paragraph,
            [
                'skills'    => $skills,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityExperiencesParagraph::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($pageName, $paragraph);

        yield TextField::new('title', new TranslatableMessage('Title'));
        yield FormField::addColumn(12);
        $collectionField = CollectionField::new('skills', new TranslatableMessage('Skills'));
        $collectionField->setEntryToStringMethod(
            function ($link): TranslatableMessage {
                unset($link);

                return new TranslatableMessage('Skill');
            }
        );
        $collectionField->setEntryType(ExperienceType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Experiences');
    }

    #[Override]
    public function getType(): string
    {
        return 'experiences';
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
