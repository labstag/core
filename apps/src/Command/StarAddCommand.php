<?php

namespace Labstag\Command;

use Labstag\Entity\Star;
use Labstag\Message\StarMessage;
use Labstag\Repository\StarRepository;
use Labstag\Service\FileService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:star-add', description: 'Get all star github with npm run star:get')]
class StarAddCommand extends Command
{

    private int $add = 0;

    private int $update = 0;

    public function __construct(
        protected FileService $fileService,
        protected MessageBusInterface $messageBus,
        protected StarRepository $starRepository,
    )
    {
        parent::__construct();
    }

    protected function addOrUpdate(Star $star): void
    {
        if (is_null($star->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $filename     = 'stars.json';
        $file         = $this->fileService->getFileInAdapter('private', $filename);
        if (!is_file($file)) {
            $symfonyStyle->error('File not found ' . $filename);

            return Command::SUCCESS;
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $symfonyStyle->error('Json error');

            return Command::FAILURE;
        }

        $dataJson = [];
        foreach ($data as $page) {
            $dataJson = array_merge($dataJson, $page);
        }

        $progressBar = new ProgressBar($output, count($dataJson));
        $progressBar->start();
        foreach ($dataJson as $data) {
            if (true == $data['private']) {
                $progressBar->advance();
                continue;
            }

            $this->messageBus->dispatch(new StarMessage($data));
            $progressBar->advance();
        }

        $progressBar->finish();

        $symfonyStyle->success('All star added');

        return Command::SUCCESS;
    }
}
