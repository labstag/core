<?php

namespace Labstag\Command;

use DateTime;
use Labstag\Repository\BanIpRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Service\SecurityService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:banauto', description: 'Add a short description for your command',)]
class BanautoCommand
{
    public function __construct(
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
        protected SecurityService $securityService,
        protected BanIpRepository $banIpRepository
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle): int
    {
        $data = $this->httpErrorLogsRepository->getAllinternetProtocolWithNbr(5);
        foreach ($data as $httpErroLogs) {
            $internetProtocol = $httpErroLogs['internetProtocol'];
            if ($this->securityService->getCurrentClientIp() === $internetProtocol) {
                continue;
            }

            $this->securityService->addBan($internetProtocol);
            $symfonyStyle->note(sprintf('Ip %s banned', $internetProtocol));
        }

        $symfonyStyle->newLine();
        $symfonyStyle->success('Banning process completed!');

        $banIps = $this->banIpRepository->findAll();
        $oneWeekAgo = new DateTime('-1 week');
            foreach ($banIps as $banIp) {
                if ($banIp->getCreatedAt() < $oneWeekAgo) {
                    $this->banIpRepository->remove($banIp, true);
                    $symfonyStyle->note(sprintf('Ip %s unbanned (older than 1 week)', $banIp->getInternetProtocol()));
                }
            }

        return Command::SUCCESS;
    }
}
