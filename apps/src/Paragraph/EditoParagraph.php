<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Edito;
use Labstag\Entity\EditoParagraph as EntityEditoParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Repository\EditoRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class EditoParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        /** @var EditoRepository $entityRepository */
        $entityRepository                = $this->getRepository(Edito::class);
        $edito                           = $entityRepository->findLast();
        if (!$edito instanceof Edito) {
            $this->setShow($paragraph, false);

            return;
        }

        $paragraphsedito = $this->paragraphService->generate($edito->getParagraphs()->getValues(), $data, $disable);
        $contents        = $this->paragraphService->getContents($paragraphsedito);
        $this->setHeader($paragraph, $contents->header);
        $this->setFooter($paragraph, $contents->footer);

        $this->setData(
            $paragraph,
            [
                'paragraphs' => $paragraphsedito,
                'paragraph'  => $paragraph,
                'edito'      => $edito,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityEditoParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        yield TextField::new('title', new TranslatableMessage('Title'));
    }

    #[Override]
    public function getName(): string
    {
        return 'Edito';
    }

    #[Override]
    public function getType(): string
    {
        return 'edito';
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
            return $object instanceof Page;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
