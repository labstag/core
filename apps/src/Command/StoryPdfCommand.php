<?php

namespace Labstag\Command;

use Labstag\Message\StoryMessage;
use Labstag\Repository\StoryRepository;
use Labstag\Service\MessageDispatcherService;
use Labstag\Service\StoryService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[AsCommand(name: 'labstag:story-pdf', description: 'Generate PDF for story',)]
class StoryPdfCommand
{
    public function __construct(
        protected StoryRepository $storyRepository,
        protected MessageDispatcherService $messageBus,
        protected StoryService $storyService,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle, OutputInterface $output): int
    {
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
