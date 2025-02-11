<?php

namespace Labstag\Command;

use Labstag\Repository\StoryRepository;
use Labstag\Service\StoryService;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\TranslatableMessage;

#[AsCommand(
    name: 'labstag:story-pdf',
    description: 'Generate PDF for story',
)]
class StoryPdfCommand extends Command
{

    public function __construct(
        protected StoryRepository $storyRepository,
        protected StoryService $storyService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $stories = $this->storyRepository->findAll();
        $counter = 0;
        $update  = 0;
        $progressBar = new ProgressBar($output, count($stories));
        $progressBar->start();
        foreach ($stories as $story) {
            $status = $this->storyService->setPdf($story);
            $update = $status ? $update + 1 : $update;
            ++$counter;

            $this->storyRepository->persist($story);
            $this->storyRepository->flush($counter);
            $progressBar->advance();
        }

        $stories = $this->storyService->getUpdates();

        $this->storyRepository->flush();

        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(
            sprintf(
                'Updated: %d',
                $numberFormatter->format($update)
            )
        );

        $symfonyStyle->success(
            new TranslatableMessage(
                'Story file generated for "%title%"',
                [
                    '%title%' => implode('"," ', $stories),
                ]
            )
        );

        return Command::SUCCESS;
    }
}
