<?php

namespace Labstag\MessageHandler;

use Labstag\Message\CompanyAllMessage;
use Labstag\Message\CompanyMessage;
use Labstag\Repository\CompanyRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CompanyAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private CompanyRepository $companyRepository,
    )
    {
    }

    public function __invoke(CompanyAllMessage $companyAllMessage): void
    {
        unset($companyAllMessage);
        $companies                          = $this->companyRepository->findAll();
        foreach ($companies as $company) {
            $this->messageDispatcherService->dispatch(new CompanyMessage($company->getId()));
        }
    }
}
