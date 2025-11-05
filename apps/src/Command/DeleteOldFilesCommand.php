<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'labstag:delete-oldfiles', description: 'Delete old files')]
class DeleteOldFilesCommand extends Command
{
    public function __construct(
        protected FileService $fileService,
    )
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        unset($input);
        $total = $this->fileService->deletedFileByEntities();
        if (0 !== $total) {
            $output->writeln($total . ' file(s) deleted');
        }

        $output->writeln('Script executed successfully.');

        return Command::SUCCESS;
    }
}
