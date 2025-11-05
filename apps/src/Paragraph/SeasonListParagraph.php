<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Season;
use Labstag\Entity\SeasonListParagraph as EntitySeasonListParagraph;
use Labstag\Entity\Serie;
use Labstag\Repository\SeasonRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SeasonListParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Serie) {
            $this->setShow($paragraph, false);

            return;
        }

        $entityRepository                = $this->getRepository(Season::class);
        if (!$entityRepository instanceof SeasonRepository) {
            $this->logger->error('SeasonListParagraph: Season repository not found.');
            $this->setShow($paragraph, false);

            return;
        }

        $seasons                         = $entityRepository->getAllActivateBySerie($data['entity']);
        if (0 === count($seasons)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'seasons'   => $seasons,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntitySeasonListParagraph::class;
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
        return (string) new TranslatableMessage('Season list');
    }

    #[Override]
    public function getType(): string
    {
        return 'season-list';
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
            return $object instanceof Block;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
