<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Company;
use Labstag\Message\CompanyMessage;
use Labstag\Repository\CompanyRepository;
use Labstag\Service\Imdb\CompanyService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CompanyMessageHandler
{
    public function __construct(
        private CompanyService $companyService,
        private CompanyRepository $companyRepository,
    )
    {
    }

    public function __invoke(CompanyMessage $companyMessage): void
    {
        $companyId = $companyMessage->getData();
        $company   = $this->companyRepository->find($companyId);
        if (!$company instanceof Company) {
            return;
        }

        $this->companyService->update($company);

        $this->companyRepository->save($company);
    }
}
