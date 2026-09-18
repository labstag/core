<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Episode;
use Labstag\Entity\EpisodeListParagraph as EntityEpisodeListParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Season;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class EpisodeListParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Season) {
            $this->setShow($paragraph, false);

            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $this->setQuery($request->query->all());

        $entityRepository                = $this->getRepository(Episode::class);
        $pagination                      = $this->getPaginator(
            $entityRepository->getQueryPaginator($data['entity']),
            30
        );

        $templates = $this->templates($paragraph, 'header');
        $this->setHeader(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $serie      = $data['entity']->getRefSerie();
        $number     = $data['entity']->getNumber();
        $repository = $this->getRepository(Season::class);

        $prev = $repository->getOneBySerieAndPosition($serie, $number - 1);
        $next = $repository->getOneBySerieAndPosition($serie, $number + 1);

        $this->setData(
            $paragraph,
            [
                'prev'       => $prev,
                'next'       => $next,
                'serie'      => $serie,
                'pagination' => $pagination,
                'paragraph'  => $paragraph,
                'data'       => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityEpisodeListParagraph::class;
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
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Episode list');
    }

    #[Override]
    public function getType(): string
    {
        return 'episode-list';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return $object instanceof Block;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function setQuery(array $query): array
    {
        if (!isset($query['order'])) {
            $query['order'] = 'number';
        }

        if (!isset($query['orderby'])) {
            $query['orderby'] = 'ASC';
        }

        return $query;
    }
}
