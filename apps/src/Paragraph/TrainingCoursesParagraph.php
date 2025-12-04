<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\TrainingCoursesParagraph as EntityTrainingCoursesParagraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Paragraph\TrainingCourseType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TrainingCoursesParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityTrainingCoursesParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        $trainings  = $paragraph->getTrainings();
        if (!is_array($trainings) || [] === $trainings) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        $this->setData($paragraph, [
                'trainings' => $trainings,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityTrainingCoursesParagraph::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($pageName, $paragraph);
        yield TextField::new('title', new TranslatableMessage('Title'));
        yield FormField::addColumn(12);
        $collectionField = CollectionField::new('trainings', new TranslatableMessage('Training courses'));
        $collectionField->setEntryToStringMethod(
            function ($link): string {
                unset($link);

                return $this->translator->trans(new TranslatableMessage('Training course'));
            }
        );
        $collectionField->setFormTypeOption('attr', [
                'data-controller' => 'sortable',
            ]);
        $collectionField->setEntryType(TrainingCourseType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Training courses');
    }

    #[Override]
    public function getType(): string
    {
        return 'trainingcourses';
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
            return $object instanceof Page && $object->getType() == PageEnum::CV
                ->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }

    #[Override]
    public function update(Paragraph $paragraph): void
    {
        $this->updateParagraphsTraining($paragraph);
    }

    private function updateParagraphsTraining(Paragraph $paragraph): void
    {
        if (!$paragraph instanceof EntityTrainingCoursesParagraph) {
            return;
        }

        $oldskils = $paragraph->getTrainings();
        if (!is_array($oldskils)) {
            return;
        }

        $skills = [];
        foreach ($oldskils as $key => $skill) {
            $position          = (!isset($skill['position']) || is_null(
                $skill['position']
            )) ? $key : $skill['position'];
            $skill['position'] = $position;
            $skills[$position] = $skill;
        }

        ksort($skills);

        $paragraph->setTrainings($skills);
    }
}
