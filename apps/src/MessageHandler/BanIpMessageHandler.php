<?php

namespace Labstag\MessageHandler;

use Labstag\Message\BanIpMessage;
use Labstag\Repository\BanIpRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Service\NotificationService;
use Labstag\Service\SecurityService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class BanIpMessageHandler
{
    public function __construct(
        private HttpErrorLogsRepository $httpErrorLogsRepository,
        private NotificationService $notificationService,
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
            $this->notificationService->setNotification(
                'Ip banned',
                sprintf('The ip %s has been banned automatically by system', $internetProtocol)
            );
        }

        $banIps = $this->banIpRepository->findOlderThanOneDay();
        foreach ($banIps as $banIp) {
            $this->banIpRepository->delete($banIp);
            $this->notificationService->setNotification(
                'Ip unbanned',
                sprintf(
                    'The ip %s has been unbanned automatically by system (older than 1 day)',
                    $banIp->getInternetProtocol()
                )
            );
        }
    }
}
