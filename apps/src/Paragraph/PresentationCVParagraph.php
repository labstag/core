<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Paragraph\DataPresentationType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class PresentationCVParagraph extends ParagraphAbstract implements ParagraphInterface
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

    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($pageName, $paragraph);
        yield TextField::new('title', new TranslatableMessage('Title'));
        $fieldData = Field::new('data', 'Bloc de données');
        $fieldData->setFormType(DataPresentationType::class);
        yield $fieldData;
    }

    #[Override]
    public function getName(): string
    {
        return 'presentation CV';
    }

    #[Override]
    public function getType(): string
    {
        return 'presentation-cv';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $serviceEntityRepositoryAbstract = $this->getRepository(Paragraph::class);
        $paragraph                       = $serviceEntityRepositoryAbstract->findOneBy(
            [
                'type' => $this->getType(),
            ]
        );

        if (!$paragraph instanceof Paragraph) {
            return $object instanceof Page && $object->getType() == PageEnum::CV->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
