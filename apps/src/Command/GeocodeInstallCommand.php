<?php

namespace Labstag\Command;

use Exception;
use Labstag\Entity\GeoCode;
use Labstag\Message\GeocodeMessage;
use Labstag\Service\GeocodeService;
use Labstag\Service\MessageDispatcherService;
use NumberFormatter;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:geocode-install', description: 'Retrieve geocodes')]
class GeocodeInstallCommand
{

    private int $add = 0;

    private int $update = 0;

    public function __construct(
        private readonly GeocodeService $geocodeService,
        protected MessageDispatcherService $messageBus,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle, OutputInterface $output, #[Argument] string $country): int
    {
        $symfonyStyle->title('Retrieving postal codes');
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

        foreach ($table as $row) {
            $this->messageBus->dispatch(new GeocodeMessage($row));
            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->newLine();
        $symfonyStyle->success('Geocodes retrieved');

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(
            sprintf(
                'Added: %s, Updated: %s',
                $numberFormatter->format($this->add),
                $numberFormatter->format($this->update)
            )
        );

        return Command::SUCCESS;
    }

    protected function addOrUpdate(GeoCode $geoCode): void
    {
        if (is_null($geoCode->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }
}
