<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Serie;
use Labstag\Enum\PageEnum;
use Labstag\Repository\SerieRepository;
use Override;

class SerieParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var SerieRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Serie::class);
        $categorySlug                    = $this->getCategorySlug();
        $pagination                      = $this->getPaginator(
            $serviceEntityRepositoryAbstract->getQueryPaginator($categorySlug),
            $paragraph->getNbr()
        );

        $templates = $this->templates($paragraph, 'header');
        $this->setHeader(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $this->setData(
            $paragraph,
            [
                'pagination' => $pagination,
                'paragraph'  => $paragraph,
                'data'       => $data,
            ]
        );
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        yield $this->addFieldIntegerNbr();
    }

    #[Override]
    public function getName(): string
    {
        return 'Serie';
    }

    #[Override]
    public function getType(): string
    {
        return 'serie';
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
            return $object instanceof Page && $object->getType() == PageEnum::SERIES->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
