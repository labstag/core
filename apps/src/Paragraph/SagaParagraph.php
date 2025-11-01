<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Labstag\Enum\PageEnum;
use Labstag\Repository\MovieRepository;
use Override;

class SagaParagraph extends ParagraphAbstract implements ParagraphInterface
{
    private const MINMOVIES = 2;

    private const MINSAGA   = 3;

    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);

        $request = $this->requestStack->getCurrentRequest();
        if (1 != $request->attributes->get('page')) {
            $this->setShow($paragraph, false);

            return;
        }

        $types = [
            'title',
            'country',
            'categories',
            'sagas',
            'year',
            'order',
            'orderby',
        ];
        foreach ($types as $type) {
            if ($request->query->has($type)) {
                $this->setShow($paragraph, false);

                return;
            }
        }

        /** @var MovieRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Saga::class);

        $sagas = $serviceEntityRepositoryAbstract->showPublic();
        foreach ($sagas as $key => $saga) {
            $total = $saga->getMovies()->filter(fn ($movie) => $movie->isEnable());
            if (self::MINMOVIES > count($total)) {
                unset($sagas[$key]);
            }
        }

        if (self::MINSAGA > count($sagas)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'sagas'     => $sagas,
                'paragraph' => $paragraph,
                'data'      => $data,
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
        return 'Saga';
    }

    #[Override]
    public function getType(): string
    {
        return 'saga';
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
            return $object instanceof Page && $object->getType() == PageEnum::MOVIES->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
