<?php

namespace Labstag\Command;

use Labstag\Message\StoryMessage;
use Labstag\Repository\StoryRepository;
use Labstag\Service\StoryService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[AsCommand(name: 'labstag:story-pdf', description: 'Generate PDF for story',)]
class StoryPdfCommand extends Command
{
    public function __construct(
        protected StoryRepository $storyRepository,
        protected MessageBusInterface $messageBus,
        protected StoryService $storyService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $stories      = $this->storyRepository->findAll();
        $progressBar  = new ProgressBar($output, count($stories));
        $progressBar->start();
        foreach ($stories as $story) {
            $this->messageBus->dispatch(new StoryMessage($story->getId()));
            $progressBar->advance();
        }

        $progressBar->finish();

        $symfonyStyle->success(new TranslatableMessage('All stories PDF generation messages have been dispatched.'));

        return Command::SUCCESS;
    }
}
