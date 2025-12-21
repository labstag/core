<?php

namespace Labstag\MessageHandler;

use Labstag\Message\MovieAllMessage;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class MovieAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageBus,
        private MovieRepository $movieRepository,
    )
    {
    }

    public function __invoke(MovieAllMessage $movieAllMessage): void
    {
        unset($movieAllMessage);
        $movies = $this->movieRepository->findAll();
        foreach ($movies as $movie) {
            $this->messageBus->dispatch(new MovieMessage($movie->getId()));
        }
    }
}
