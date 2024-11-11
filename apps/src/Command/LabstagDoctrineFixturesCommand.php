<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'labstag:doctrine-fixtures',
    description: 'Add a short description for your command',
)]
class LabstagDoctrineFixturesCommand extends Command
{
    public function __construct(
        protected FileService $fileService
    )
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Loading fixtures...');
        $input = new ArrayInput(
            [
                'command'          => 'doctrine:fixtures:load',
                '--no-interaction' => true,
            ]
        );
        $this->getApplication()->run($input, $output);
        $total = $this->fileService->deletedFileByEntities();
        if (0 != $total) {
            $output->writeln($total.' fichier(s) supprimé(s)');
        }

        $output->writeln('Script executed successfully.');

        return Command::SUCCESS;
    }
}
