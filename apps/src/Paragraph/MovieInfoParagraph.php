<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieInfoParagraph as EntityMovieInfoParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MovieInfoParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityMovieInfoParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        if (is_null($paragraph->getRefmovie())) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData($paragraph, [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityMovieInfoParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph);
        $entityRepository = $this->entityManager->getRepository(Movie::class);
        $movies           = $entityRepository->findBy([], [
                'title' => 'ASC',
            ]);
        $choices = [];
        foreach ($movies as $movie) {
            $choices[$movie->getTitle()] = $movie;
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('refmovie', new TranslatableMessage('Movie'));

            return;
        }

        yield ChoiceField::new('refmovie', new TranslatableMessage('Movie'))->setChoices(
            $choices
        )->allowMultipleChoices(false)
            ->renderExpanded(false)
            ->renderAsBadges(
                false
            )->formatValue(static fn ($value): string => $value instanceof Movie ? $value->getTitle() ?? '' : '');
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Movie Info');
    }

    #[Override]
    public function getType(): string
    {
        return 'movie_info';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return in_array($object::class, [Edito::class, Memo::class, Page::class, Post::class]);
    }
}
