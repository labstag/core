<?php

namespace Labstag\Command;

use Exception;
use Labstag\Repository\GeoCodeRepository;
use Labstag\Service\GeocodeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:geocode:install',
    description: 'Retrieve geocodes',
)]
class GeocodeInstallCommand extends Command
{
    public function __construct(
        private GeocodeService $geocodeService,
        private GeoCodeRepository $geoCodeRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('country', InputArgument::REQUIRED, 'country code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Retrieving postal codes');

        $country = $input->getArgument('country');
        if (!is_string($country)) {
            throw new Exception('Argument country invalide');
        }

        if (empty($country)) {
            $symfonyStyle->note(
                sprintf(
                    'Argument countrie obligatoire: %s',
                    $country
                )
            );

            return COMMAND::FAILURE;
        }

        $csv = $this->geocodeService->csv($country);
        if ([] == $csv) {
            $symfonyStyle->warning(
                ['fichier inexistant']
            );

            return COMMAND::FAILURE;
        }

        $progressBar = new ProgressBar($output, is_countable($csv) ? count($csv) : 0);
        $table       = $this->geocodeService->tables($csv);
        $progressBar->start();
        $counter = 0;
        foreach ($table as $row) {
            $entity = $this->geocodeService->add($row);
            $this->geoCodeRepository->persist($entity);
            $counter++;
            $this->geoCodeRepository->flush($counter);
            $progressBar->advance();
        }

        $this->geoCodeRepository->flush();

        $progressBar->finish();
        $symfonyStyle->newLine();
        $symfonyStyle->success("Fin d'enregistrement");

        return Command::SUCCESS;
    }
}