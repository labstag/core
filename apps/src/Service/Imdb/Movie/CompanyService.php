<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Entity\Movie;
use Labstag\Service\Imdb\CompanyService as BaseCompanyService;

final class CompanyService
{
    public function __construct(
        private BaseCompanyService $baseCompanyService,
    )
    {
    }

    public function updateCompany(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb']['production_companies']) || 0 === count($details['tmdb']['production_companies'])) {
            return false;
        }

        foreach ($movie->getCompanies() as $company) {
            $movie->removeCompany($company);
        }

        foreach ($details['tmdb']['production_companies'] as $company) {
            $company = $this->baseCompanyService->getCompany($company);
            $movie->addCompany($company);
        }

        return true;
    }
}
