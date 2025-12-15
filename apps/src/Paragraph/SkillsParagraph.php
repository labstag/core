<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\SkillsParagraph as EntitySkillsParagraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Paragraph\SkillsType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SkillsParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntitySkillsParagraph) {
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
        return EntitySkillsParagraph::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($pageName, $paragraph);
        yield TextField::new('title', new TranslatableMessage('Title'));
        yield FormField::addColumn(12);
        $collectionField = CollectionField::new('skills', new TranslatableMessage('Skills'));
        $collectionField->setEntryToStringMethod(
            function ($link): string {
                unset($link);
                $translatableMessage = new TranslatableMessage('Skill');

                return $this->translator->trans($translatableMessage->getMessage(), $translatableMessage->getParameters());
            }
        );
        $collectionField->setFormTypeOption(
            'attr',
            ['data-controller' => 'sortable']
        );
        $collectionField->setEntryType(SkillsType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Skills');
    }

    #[Override]
    public function getType(): string
    {
        return 'skills';
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

    #[Override]
    public function update(Paragraph $paragraph): void
    {
        $this->updateParagraphsSkills($paragraph);
    }

    private function updateParagraphsSkills(Paragraph $paragraph): void
    {
        if (!$paragraph instanceof EntitySkillsParagraph) {
            return;
        }

        $oldskils = $paragraph->getSkills();
        if (!is_array($oldskils)) {
            return;
        }

        $skills = [];
        foreach ($oldskils as $key => $skill) {
            $position          = (!isset($skill['position']) || is_null(
                $skill['position']
            )) ? $key : $skill['position'];
            $skill['position'] = $position;
            $skill['skills']   = isset($skill['skills']) ? $this->updateSkills($skill['skills']) : [];
            $skills[$position] = $skill;
        }

        ksort($skills);

        $paragraph->setSkills($skills);
    }

    private function updateSkills(array $tab): array
    {
        $old = $tab;
        $tab = [];
        foreach ($old as $key => $skill) {
            $position          = (!isset($skill['position']) || is_null(
                $skill['position']
            )) ? $key : $skill['position'];
            $skill['position'] = $position;
            $tab[$position]    = $skill;
        }

        ksort($tab);

        return $tab;
    }
}
