<?php

namespace Labstag\MessageHandler;

use DateTime;
use Labstag\Message\BanIpMessage;
use Labstag\Repository\BanIpRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Service\SecurityService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class BanIpMessageHandler
{
    public function __construct(
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
        protected SecurityService $securityService,
        protected BanIpRepository $banIpRepository
    )
    {
    }

    public function __invoke(BanIpMessage $message): void
    {
        unset($message);
        $data = $this->httpErrorLogsRepository->getAllinternetProtocolWithNbr(5);
        foreach ($data as $httpErroLogs) {
            $internetProtocol = $httpErroLogs['internetProtocol'];
            if ($this->securityService->getCurrentClientIp() === $internetProtocol) {
                continue;
            }

            $this->securityService->addBan($internetProtocol);
            dump(sprintf('Ip %s banned', $internetProtocol));
        }

        $banIps = $this->banIpRepository->findAll();
        $oneWeekAgo = new DateTime('-1 week');
        foreach ($banIps as $banIp) {
            if ($banIp->getCreatedAt() < $oneWeekAgo) {
                $this->banIpRepository->delete($banIp, true);
                dump(sprintf('Ip %s unbanned (older than 1 week)', $banIp->getInternetProtocol()));
            }
        }
    }
}
