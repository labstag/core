<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Serie;
use Labstag\Message\AddSerieMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class AddSerieMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SerieRepository $serieRepository,
    )
    {
    }

    public function __invoke(AddSerieMessage $addSerieMessage): void
    {
        $data = $addSerieMessage->getData();

        $imdb  = (string) $data['Imdb'];
        $serie = $this->serieRepository->findOneBy(
            ['imdb' => $imdb]
        );
        if ($serie instanceof Serie) {
            $serie->setFile(true);
            $this->serieRepository->save($serie);

            return;
        }

        $serie = new Serie();
        $serie->setEnable(true);
        $serie->setAdult(false);
        $serie->setImdb($imdb);

        $tmdb       = (string) $data['tmdbId'];
        $title      = trim((string) $data['Title']);
        $serie->setTmdb($tmdb);
        $serie->setTitle($title);
        $serie->setFile(true);

        $this->serieRepository->save($serie);
        $this->messageBus->dispatch(new SerieMessage($serie->getId()));
    }
}
