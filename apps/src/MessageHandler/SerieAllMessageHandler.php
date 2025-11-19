<?php

namespace Labstag\MessageHandler;

use DateTime;
use Labstag\Message\SerieAllMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SerieAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SerieRepository $serieRepository,
    )
    {
    }

    public function __invoke(SerieAllMessage $serieAllMessage): void
    {
        unset($serieAllMessage);
        $series                          = $this->serieRepository->findAll();
        foreach ($series as $serie) {
            $json = $serie->getJson();
            if (!$this->isCorrectDate($json)) {
                $this->messageBus->dispatch(new SerieMessage($serie->getId()));
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
