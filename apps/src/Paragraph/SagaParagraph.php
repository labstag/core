<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Labstag\Repository\MovieRepository;
use Override;

class SagaParagraph extends ParagraphAbstract
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

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Page::class];
    }
}
