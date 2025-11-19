<?php

namespace Labstag\MessageHandler;

use DateTime;
use Labstag\Message\MovieAllMessage;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class MovieAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private MovieRepository $movieRepository,
    )
    {
    }

    public function __invoke(MovieAllMessage $movieAllMessage): void
    {
        unset($movieAllMessage);
        $movies = $this->movieRepository->findAll();
        foreach ($movies as $movie) {
            $json = $movie->getJson();
            if (!$this->isCorrectDate($json)) {
                $this->messageBus->dispatch(new MovieMessage($movie->getId()));
            }
        }
    }

    private function isCorrectDate(?array $json): bool
    {
        if (is_array($json) && isset($json['json_import'])) {
            $importDate = new DateTime($json['json_import']);
            $now        = new DateTime();
            $daysDiff   = $now->diff($importDate)->days;

            if (7 > $daysDiff) {
                return true;
            }
        }

        return false;
    }
}
