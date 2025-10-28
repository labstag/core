<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Movie;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SagaListParagraph extends ParagraphAbstract
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

        $serviceEntityRepositoryAbstract = $this->getRepository(Movie::class);
        $movies                         = $serviceEntityRepositoryAbstract->getAllActivateBySaga($data['entity']);
        if (0 === count($movies)) {
            $this->setShow($paragraph, false);

            return;
        }

        $templates = $this->templates($paragraph, 'footer');
        $this->setFooter(
            $paragraph,
            $this->render(
                $templates['view'],
                [
                    'movies' => $movies
                ]
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

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
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

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Block::class];
    }
}
