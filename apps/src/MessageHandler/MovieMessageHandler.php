<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Movie;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Service\Imdb\MovieService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MovieMessageHandler
{
    public function __construct(
        private MovieService $movieService,
        private MovieRepository $movieRepository,
    )
    {
    }

    public function __invoke(MovieMessage $movieMessage): void
    {
        $movieId = $movieMessage->getMovie();
        $movie   = $this->movieRepository->find($movieId);
        if (!$movie instanceof Movie) {
            return;
        }

        $this->movieService->update($movie);
        $this->movieRepository->save($movie);
    }
}
