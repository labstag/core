<?php

namespace Labstag\Command;

use Labstag\Repository\MessengerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:messenger:cleanup-history', description: 'Remove expired successful messenger history rows')]
class MessengerCleanupHistoryCommand extends Command
{
    public function __construct(private MessengerRepository $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show count only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $this->repository->countExpired();
        $io->info(sprintf('Expired rows: %d', $count));
        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
        }
        $deleted = $this->repository->cleanupExpired();
        $io->success(sprintf('Deleted %d rows', $deleted));

        return Command::SUCCESS;
    }
}