<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Movie;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Labstag\Repository\MovieRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MovieSliderParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $listing = $this->slugService->getPageByType(PageEnum::MOVIES->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            $this->setShow($paragraph, false);

            return;
        }

        /** @var MovieRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(
            Movie::class
        );
        $nbr                        = $paragraph->getNbr();
        $title                      = $paragraph->getTitle();
        $movies                     = $serviceEntityRepositoryLib->findLastByNbr($nbr);
        if (0 === count($movies)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'listing'   => $listing,
                'title'     => $title,
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

        yield TextField::new('title', new TranslatableMessage('Title'));
        yield $this->addFieldIntegerNbr();
    }

    #[Override]
    public function getName(): string
    {
        return 'movie slider';
    }

    #[Override]
    public function getType(): string
    {
        return 'movie-slider';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
