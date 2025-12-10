<?php

namespace Labstag\MessageHandler;

use Labstag\Message\BanIpMessage;
use Labstag\Repository\BanIpRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Service\SecurityService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class BanIpMessageHandler
{
    public function __construct(
        private HttpErrorLogsRepository $httpErrorLogsRepository,
        private SecurityService $securityService,
        private BanIpRepository $banIpRepository,
    )
    {
    }

    public function __invoke(BanIpMessage $banIpMessage): void
    {
        unset($banIpMessage);
        $data = $this->httpErrorLogsRepository->getAllinternetProtocolWithNbr(5);
        foreach ($data as $httpErroLogs) {
            $internetProtocol = $httpErroLogs['internetProtocol'];
            if ($this->securityService->getCurrentClientIp() === $internetProtocol) {
                continue;
            }

            $this->securityService->addBan($internetProtocol);
            dump(sprintf('Ip %s banned', $internetProtocol));
        }

        $banIps = $this->banIpRepository->findOlderThanOneDay();
        foreach ($banIps as $banIp) {
            $this->banIpRepository->delete($banIp);
            dump(sprintf('Ip %s unbanned (older than 1 week)', $banIp->getInternetProtocol()));
        }
    }
}
