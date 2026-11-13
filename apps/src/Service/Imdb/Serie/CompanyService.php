<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Entity\Serie;
use Labstag\Service\Imdb\CompanyService as BaseCompanyService;

final class CompanyService
{
    public function __construct(
        private BaseCompanyService $baseCompanyService,
    ) {
    }

    public function updateCompany(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb']['production_companies']) || 0 === count($details['tmdb']['production_companies'])) {
            return false;
        }

        foreach ($serie->getCompanies() as $company) {
            $serie->removeCompany($company);
        }

        foreach ($details['tmdb']['production_companies'] as $company) {
            $company = $this->baseCompanyService->getCompany($company);
            $serie->addCompany($company);
        }

        return true;
    }
}
