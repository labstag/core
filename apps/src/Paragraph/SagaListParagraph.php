<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Movie;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Labstag\Entity\SagaListParagraph as EntitySagaListParagraph;
use Labstag\Repository\MovieRepository;
use Override;

class SagaListParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Saga) {
            $this->setShow($paragraph, false);

            return;
        }

        $entityRepository                = $this->getRepository(Movie::class);
        if (!$entityRepository instanceof MovieRepository) {
            $this->logger->error('SagaListParagraph: Movie repository not found.');
            $this->setShow($paragraph, false);

            return;
        }

        $movies                          = $entityRepository->getAllActivateBySaga($data['entity']);
        if ([] === $movies) {
            $this->setShow($paragraph, false);

            return;
        }

        $templates = $this->templates($paragraph, 'footer');
        $this->setFooter(
            $paragraph,
            $this->render(
                $templates['view'],
                ['movies' => $movies]
            )
        );

        $this->setData(
            $paragraph,
            [
                'movies'    => $movies,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntitySagaListParagraph::class;
    }

    #[Override]
    public function getName(): string
    {
        return 'Saga list';
    }

    #[Override]
    public function getType(): string
    {
        return 'saga-list';
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
