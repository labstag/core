<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Entity\Movie;
use Labstag\Service\Imdb\PersonService;
use Labstag\Service\Imdb\CompanyService;

final class CreditService
{
    public function __construct(
        private PersonService $personService,
        private CompanyService $companyService,
    ) {
    }

    public function updateCredits(Movie $movie, array $details): bool
    {
        foreach ($movie->getCastings() as $casting) {
            $movie->removeCasting($casting);
        }

        if (isset($details['credits']['cast']) && is_array($details['credits']['cast'])) {
            foreach ($details['credits']['cast'] as $cast) {
                $person  = $this->personService->getPerson($cast);
                $casting = $this->personService->addToCastingMovie($person, $movie, $cast);
                $movie->addCasting($casting);
            }
        }

        if (isset($details['credits']['crew']) && is_array($details['credits']['crew'])) {
            foreach ($details['credits']['crew'] as $crew) {
                $person  = $this->personService->getPerson($crew);
                $casting = $this->personService->addToCastingMovie($person, $movie, $crew);
                $movie->addCasting($casting);
            }
        }

        return true;
    }
}
