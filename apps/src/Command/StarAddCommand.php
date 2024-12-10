<?php

namespace Labstag\Command;

use Labstag\Entity\Star;
use Labstag\Repository\StarRepository;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:star-add',
    description: 'Get all star github with npm run star:get',
)]
class StarAddCommand extends Command
{
    public function __construct(
        protected StarRepository $starRepository
    )
    {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $file         = getcwd().'/stars.json';
        if (!is_file($file)) {
            $symfonyStyle->error('File not found');

            return Command::FAILURE;
        }

        $this->disableAll();

        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $symfonyStyle->error('Json error');

            return Command::FAILURE;
        }

        $counter = 0;

        $dataJson = [];
        foreach ($data as $page) {
            $dataJson = array_merge($dataJson, $page);
        }

        $progressBar = new ProgressBar($output, is_countable($dataJson) ? count($dataJson) : 0);
        $progressBar->start();
        foreach ($dataJson as $data) {
            if (true != $data['private']) {
                $star = $this->setStar($data);
            }

            ++$counter;

            $this->starRepository->persist($star);
            $this->starRepository->flush($counter);
            $progressBar->advance();
        }

        $this->starRepository->flush();
        $progressBar->finish();

        $symfonyStyle->success('All star added');

        return Command::SUCCESS;
    }

    private function disableAll()
    {
        $stars = $this->starRepository->findBy(
            ['enable' => true]
        );
        $counter = 0;
        foreach ($stars as $star) {
            $star->setEnable(false);
            ++$counter;

            $this->starRepository->persist($star);
            $this->starRepository->flush($counter);
        }

        $this->starRepository->flush();
    }

    private function setStar($data)
    {
        $star = $this->starRepository->findOneBy(
            [
                'repository' => $data['git_url'],
            ]
        );
        if (!$star instanceof Star) {
            $star = new Star();
            $star->setTitle($data['full_name']);
        }

        $star->setLanguage($data['language']);
        $star->setEnable(true);
        $star->setRepository($data['git_url']);
        $star->setForks($data['forks_count']);
        $star->setUrl($data['html_url']);
        $star->setDescription($data['description'] ?? null);
        $star->setLicense($data['license']['name'] ?? null);
        $star->setStargazers($data['stargazers_count'] ?? 0);
        $star->setWatchers($data['watchers_count'] ?? 0);

        return $star;
    }
}
