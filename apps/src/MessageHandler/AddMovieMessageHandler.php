<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Movie;
use Labstag\Message\AddMovieMessage;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class AddMovieMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageBus,
        private MovieRepository $movieRepository,
    )
    {
    }

    public function __invoke(AddMovieMessage $addMovieMessage): void
    {
        $data = $addMovieMessage->getData();

        $imdb  = (string) $data['ID IMDb'];
        $movie = $this->movieRepository->findOneBy(
            ['imdb' => $imdb]
        );
        if ($movie instanceof Movie) {
            $movie->setFile(true);
            $this->movieRepository->save($movie);

            return;
        }

        $movie = new Movie();
        $movie->setEnable(true);
        $movie->setAdult(false);
        $movie->setImdb($imdb);

        $tmdb       = (string) $data['ID TMDB'];
        $duration   = empty($data['Durée']) ? null : (int) $data['Durée'];
        $title      = trim((string) $data['Titre']);
        $movie->setTmdb($tmdb);
        $movie->setDuration($duration);
        $movie->setTitle($title);
        $movie->setFile(true);

        $this->movieRepository->save($movie);
        $this->messageBus->dispatch(new MovieMessage($movie->getId()));
        // do something with your message
    }
}
