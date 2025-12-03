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

        $entityRepository                = $this->getRepository(Episode::class);
        $episodes                        = $entityRepository->getAllActivateBySeason($data['entity']);
        if (0 === count($episodes)) {
            $this->setShow($paragraph, false);

            return;
        }

        $serie      = $data['entity']->getRefSerie();
        $number     = $data['entity']->getNumber();
        $repository = $this->getRepository(Season::class);

        $prev = $repository->getOneBySerieAndPosition($serie, $number - 1);
        $next = $repository->getOneBySerieAndPosition($serie, $number + 1);

        $this->setData(
            $paragraph,
            [
                'prev'      => $prev,
                'next'      => $next,
                'serie'     => $serie,
                'episodes'  => $episodes,
                'paragraph' => $paragraph,
                'data'      => $data,
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
}
