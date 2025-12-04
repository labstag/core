<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Game;
use Labstag\Entity\GameParagraph as EntityGameParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class GameParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var GameRepository $entityRepository */
        $entityRepository = $this->getRepository(Game::class);

        $request      = $this->requestStack->getCurrentRequest();
        $query        = $this->setQuery($request->query->all());
        $categorySlug = $this->getCategorySlug();

        $pagination = $this->getPaginator(
            $entityRepository->getQueryPaginator($query, $categorySlug),
            $paragraph->getNbr()
        );

        $templates = $this->templates($paragraph, 'header');
        $this->setHeader($paragraph, $this->render($templates['view'], [
                    'pagination' => $pagination,
                ]));

        $this->setData($paragraph, [
                'pagination' => $pagination,
                'paragraph'  => $paragraph,
                'data'       => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityGameParagraph::class;
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
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Game');
    }

    #[Override]
    public function getType(): string
    {
        return 'game';
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
            return $object instanceof Page && $object->getType() == PageEnum::GAMES
                ->value;
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
            $query['order'] = 'title';
        }

        if (isset($query['orderby']) && !in_array($query['orderby'], ['ASC', 'DESC'])) {
            unset($query['orderby']);
        }

        if (!isset($query['orderby'])) {
            $query['orderby'] = 'ASC';
        }

        return $query;
    }
}
