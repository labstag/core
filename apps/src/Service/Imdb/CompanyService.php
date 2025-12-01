<?php

namespace Labstag\Service\Imdb;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Company;
use Labstag\Message\CompanyMessage;
use Labstag\Repository\CompanyRepository;
use Labstag\Service\FileService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CompanyService
{
    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
        private CompanyRepository $companyRepository,
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function getCompany(array $data): Company
    {
        $company = $this->companyRepository->findOneBy(
            [
                'tmdb' => $data['id'],
            ]
        );
        if (!$company instanceof Company) {
            $company = new Company();
            $company->setTitle($data['name']);
            $company->setTmdb($data['id']);
            $this->companyRepository->save($company);
            $this->messageBus->dispatch(new CompanyMessage($company->getId()));
        }

        return $company;
    }

    public function update(Company $company): bool
    {
        $details = $this->theMovieDbApi->getDetailsCompany($company);
        if (is_null($details['tmdb'])) {
            $this->companyRepository->delete($company);
            $this->logger->error('Company not found TMDB id ' . $company->getTmdb());

            return false;
        }

        $statuses = [
            $this->updateCompany($company, $details),
            $this->updateImageCompany($company, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function updateCompany(Company $company, array $data): bool
    {
        $company->setTitle($data['tmdb']['name']);
        $company->setUrl($data['tmdb']['homepage'] ?? null);

        return true;
    }

    private function updateImageCompany(Company $company, array $data): bool
    {
        $poster = $this->theMovieDbApi->images()->getLogoUrl($data['tmdb']['logo_path'] ?? '');
        if (is_null($poster)) {
            $company->setImgFile();
            $company->setImg(null);

            return false;
        }

        if ('' !== (string) $company->getImg()) {
            return false;
        }

        $this->fileService->setUploadedFile($poster, $company, 'imgFile');

        return true;
    }
}
