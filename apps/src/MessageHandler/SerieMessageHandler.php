<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Meta;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SeasonRepository;
use Labstag\Repository\SerieRepository;
use Labstag\Service\SerieService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsMessageHandler]
final class SerieMessageHandler
{
    public function __construct(
        private SerieService $serieService,
        private SerieRepository $serieRepository,
        private SeasonRepository $seasonRepository,
    )
    {
    }

    public function __invoke(SerieMessage $serieMessage): void
    {
        $serieId = $serieMessage->getSerie();

        $serie = $this->serieRepository->find($serieId);
        if (!$serie instanceof Serie) {
            return;
        }

        $meta = $serie->getMeta();
        if (!$meta instanceof \Labstag\Entity\Meta) {
            $meta = new Meta();
            $serie->setMeta($meta);
        }

        $this->serieRepository->save($serie);

        foreach ($serie->getSeasons() as $season) {
            $this->correctionSlug($season);
        }

        $this->serieService->update($serie);
        $this->serieRepository->save($serie);
    }

    private function correctionSlug(object $season): void
    {
        $asciiSlugger  = new AsciiSlugger();
        $unicodeString = $asciiSlugger->slug((string) $season->getTitle())->lower();
        $slug      = $unicodeString;
        $find      = false;
        $number    = 1;
        while (false === $find) {
            $testSeason = $this->seasonRepository->findOneBy(
                [
                    'refserie' => $season->getRefserie(),
                    'slug'     => $slug,
                ]
            );
            if (!$testSeason instanceof Season) {
                $find = true;
                $season->setSlug($slug);
                break;
            }

            if ($testSeason->getId() === $season->getId()) {
                $find = true;
                break;
            }

            $slug = $unicodeString . '-' . $number;
            ++$number;
        }
    }
}
