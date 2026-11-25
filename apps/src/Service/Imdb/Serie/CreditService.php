<?php
declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Entity\Serie;
use Labstag\Service\Imdb\PersonService;

class CreditService
{
    public function __construct(
        private PersonService $personService,
    )
    {
    }

    public function updateCredits(Serie $serie, array $details): bool
    {
        $cast = [];
        $crew = [];

        if (isset($details['credits']['cast']) && is_array($details['credits']['cast'])) {
            foreach ($details['credits']['cast'] as $c) {
                $person = $this->personService->getPerson($c);
                $cast[] = [
                    'personId'  => $person->getId(),
                    'character' => $c['character'] ?? null,
                ];
            }
        }

        if (isset($details['credits']['crew']) && is_array($details['credits']['crew'])) {
            foreach ($details['credits']['crew'] as $c) {
                $person = $this->personService->getPerson($c);
                $crew[] = [
                    'personId'   => $person->getId(),
                    'department' => $c['department'] ?? null,
                    'job'        => $c['job'] ?? null,
                ];
            }
        }

        $serie->setCasting(['cast' => $cast, 'crew' => $crew]);

        return true;
    }
}
