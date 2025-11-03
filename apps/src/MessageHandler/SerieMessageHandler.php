<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Serie;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SeasonRepository;
use Labstag\Repository\SerieRepository;
use Labstag\Service\SerieService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

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

        $this->serieService->update($serie);
        $this->serieRepository->save($serie);
    }
}
