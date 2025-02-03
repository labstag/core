<?php

namespace Labstag\Command;

use Exception;
use Labstag\Entity\GeoCode;
use Labstag\Repository\GeoCodeRepository;
use Labstag\Service\GeocodeService;
use NumberFormatter;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:geocode-install', description: 'Retrieve geocodes')]
class GeocodeInstallCommand extends Command
{
    private int $add = 0;

    private int $update = 0;

    public function __construct(
        private readonly GeocodeService $geocodeService,
        private readonly GeoCodeRepository $geoCodeRepository,
    ) {
        parent::__construct();
    }

    protected function addOrUpdate(GeoCode $geoCode): void
    {
        if (is_null($geoCode->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('country', InputArgument::REQUIRED, 'country code');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Retrieving postal codes');

        $country = $input->getArgument('country');
        if (!is_string($country)) {
            throw new Exception('Argument country invalide');
        }

        if ('' === $country || '0' === $country) {
            $symfonyStyle->note(sprintf('Argument country invalide: %s', $country));

            return Command::FAILURE;
        }

        $csv = $this->geocodeService->csv($country);
        if ([] == $csv) {
            $symfonyStyle->warning(['file not found']);

            return Command::FAILURE;
        }

        $table       = $this->geocodeService->tables($csv);
        $progressBar = new ProgressBar($output, count($table));
        $progressBar->start();

        $counter = 0;
        foreach ($table as $row) {
            $entity = $this->geocodeService->add($row);
            $this->addOrUpdate($entity);

            $this->geoCodeRepository->persist($entity);
            ++$counter;
            $this->geoCodeRepository->flush($counter);
            $progressBar->advance();
        }

        $this->geoCodeRepository->flush();

        $progressBar->finish();
        $symfonyStyle->newLine();
        $symfonyStyle->success('Geocodes retrieved');

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(
            sprintf(
                'Added: %d, Updated: %d',
                $numberFormatter->format($this->add),
                $numberFormatter->format($this->update)
            )
        );

        return Command::SUCCESS;
    }
}
