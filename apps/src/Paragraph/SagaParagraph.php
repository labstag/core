<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Saga;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Repository\MovieRepository;
use Override;

class SagaParagraph extends ParagraphAbstract
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->attributes->get('page') != 1) {
            $this->setShow($paragraph, false);
            return;
        }

        /** @var MovieRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Saga::class);

        $sagas = $serviceEntityRepositoryAbstract->showPublic();
        foreach ($sagas as $key => $saga) {
            if (count($saga->getMovies()->filter(
                function ($movie) {
                    return $movie->isEnable();
                }
            )) < 2) {
                unset($sagas[$key]);
            }
        }

        
        if (count($sagas) == 0) {
            $this->setShow($paragraph, false);
            return;
        }

        $this->setData(
            $paragraph,
            [
                'sagas'      => $sagas,
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
