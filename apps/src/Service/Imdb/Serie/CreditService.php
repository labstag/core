<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Entity\Serie;
use Labstag\Service\Imdb\PersonService;

final class CreditService
{
    public function __construct(
        private PersonService $personService,
    )
    {
    }

    public function updateCredits(Serie $serie, array $details): bool
    {
        foreach ($serie->getCastings() as $casting) {
            $serie->removeCasting($casting);
        }

        if (isset($details['credits']['cast']) && is_array($details['credits']['cast'])) {
            foreach ($details['credits']['cast'] as $cast) {
                $person  = $this->personService->getPerson($cast);
                $casting = $this->personService->addToCastingSerie($person, $serie, $cast);
                $serie->addCasting($casting);
            }
        }

        if (isset($details['credits']['crew']) && is_array($details['credits']['crew'])) {
            foreach ($details['credits']['crew'] as $crew) {
                $person  = $this->personService->getPerson($crew);
                $casting = $this->personService->addToCastingSerie($person, $serie, $crew);
                $serie->addCasting($casting);
            }
        }

        return true;
    }
}
