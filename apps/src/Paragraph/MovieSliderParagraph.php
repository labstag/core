<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieSliderParagraph as EntityMovieSliderParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Repository\MovieRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MovieSliderParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityMovieSliderParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        $listing = $this->slugService->getPageByType(PageEnum::MOVIES->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            $this->setShow($paragraph, false);

            return;
        }

        /** @var MovieRepository $entityRepository */
        $entityRepository                = $this->getRepository(Movie::class);
        $nbr                             = $paragraph->getNbr();
        $title                           = $paragraph->getTitle();
        $movies                          = $entityRepository->findLastByNbr($nbr);
        if (0 === count($movies)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'listing'   => $listing,
                'title'     => $title,
                'movies'    => $movies,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityMovieSliderParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);

        yield TextField::new('title', new TranslatableMessage('Title'));
        yield $this->addFieldIntegerNbr();
    }

    #[Override]
    public function getName(): string
    {
        return 'movie slider';
    }

    #[Override]
    public function getType(): string
    {
        return 'movie-slider';
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
            return $object instanceof Page && $object->getType() == PageEnum::HOME->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
