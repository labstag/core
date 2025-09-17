<?php

namespace Labstag\Command;

use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Service\SecurityService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:banauto', description: 'Add a short description for your command',)]
class BanautoCommand extends Command
{
    public function __construct(
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
        protected SecurityService $securityService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $data = $this->httpErrorLogsRepository->getAllinternetProtocolWithNbr(10);
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

        return Command::SUCCESS;
    }
}
