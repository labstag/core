<?php

namespace Labstag\Command;

use Labstag\Message\EpisodeMessage;
use Labstag\Repository\EpisodeRepository;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:episode:runtime', description: 'Update episode without runtime',)]
class EpisodeRuntimeCommand extends Command
{
    public function __construct(
        protected MessageBusInterface $messageBus,
        protected EpisodeRepository $episodeRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $episodes     = $this->episodeRepository->findBy(
            ['runtime' => null]
        );

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Episodes without runtime: %s', $numberFormatter->format(count($episodes))));

        $progressBar = new ProgressBar($output, count($episodes));
        $progressBar->start();
        foreach ($episodes as $episode) {
            $this->messageBus->dispatch(new EpisodeMessage($episode->getId()));
            $progressBar->advance();
        }

        $progressBar->finish();

        $symfonyStyle->success('All episodes without runtime have been dispatched.');

        return Command::SUCCESS;
    }
}
