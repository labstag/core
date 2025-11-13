<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieParagraph as EntityMovieParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Form\Front\MovieType;
use Labstag\Repository\MovieRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MovieParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityMovieParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        /** @var MovieRepository $entityRepository */
        $entityRepository = $this->getRepository(Movie::class);

        $request = $this->requestStack->getCurrentRequest();
        $query   = $this->setQuery($request->query->all());

        $pagination = $this->getPaginator($entityRepository->getQueryPaginator($query), $paragraph->getNbr());

        $templates = $this->templates($paragraph, 'header');
        $this->setHeader(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $templates = $this->templates($paragraph, 'footer');
        $this->setFooter(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $form = $this->createForm(MovieType::class, $query);

        $this->setData(
            $paragraph,
            [
                'form'       => $form,
                'pagination' => $pagination,
                'paragraph'  => $paragraph,
                'data'       => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityMovieParagraph::class;
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
        return (string) new TranslatableMessage('Movie');
    }

    #[Override]
    public function getType(): string
    {
        return 'movie';
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
            return $object instanceof Page && $object->getType() == PageEnum::MOVIES->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function setQuery(array $query): array
    {
        if (isset($query['order']) && !in_array($query['order'], ['title', 'releaseDate', 'createdAt'])) {
            unset($query['order']);
        }

        if (!isset($query['order'])) {
            $query['order'] = 'createdAt';
        }

        if (isset($query['orderby']) && !in_array($query['orderby'], ['ASC', 'DESC'])) {
            unset($query['orderby']);
        }

        if (!isset($query['orderby'])) {
            $query['orderby'] = 'DESC';
        }

        return $query;
    }
}
