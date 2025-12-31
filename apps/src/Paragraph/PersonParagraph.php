<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Person;
use Labstag\Entity\Saga;
use Labstag\Entity\PersonParagraph as EntityPersonParagraph;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class PersonParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);

        $request          = $this->requestStack->getCurrentRequest();
        $entityRepository = $this->getRepository(Person::class);
        $query            = $this->setQuery($request->query->all());

        $pagination = $this->getPaginator($entityRepository->getQueryPaginator($query), $paragraph->getNbr());

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

    public function getClass(): string
    {
        return EntityPersonParagraph::class;
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
        return new TranslatableMessage('Person');
    }

    #[Override]
    public function getType(): string
    {
        return 'person';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $entityRepository                = $this->getRepository($this->getClass());
        $paragraph                       = $entityRepository->findOneBy([]);

        return !$paragraph instanceof Paragraph;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function setQuery(array $query): array
    {
        if (!isset($query['order'])) {
            $query['order'] = 'title';
        }

        if (!isset($query['orderby'])) {
            $query['orderby'] = 'ASC';
        }

        return $query;
    }
}
