<?php

namespace Labstag\MessageHandler;

use DateTime;
use Labstag\Message\SeasonAllMessage;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\SeasonRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SeasonAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SeasonRepository $seasonRepository,
    )
    {
    }

    public function __invoke(SeasonAllMessage $seasonAllMessage): void
    {
        unset($seasonAllMessage);
        $seasons                          = $this->seasonRepository->findAll();
        foreach ($seasons as $season) {
            $json = $season->getJson();
            if (!$this->isCorrectDate($json)) {
                $this->messageBus->dispatch(new SeasonMessage($season->getId()));
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
