<?php

namespace Labstag\Command;

use Labstag\Entity\Serie;
use Labstag\Message\AddSerieMessage;
use Labstag\Service\FileService;
use NumberFormatter;
use Override;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:series:add', description: 'Add series with tvshows.csv')]
class SerieAddCommand extends Command
{

    private int $add = 0;

    private int $update = 0;

    public function __construct(
        protected MessageBusInterface $messageBus,
        protected FileService $fileService,
    )
    {
        parent::__construct();
    }

    protected function addOrUpdate(Serie $serie): void
    {
        if (is_null($serie->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $filename     = 'tvshows.csv';
        $file         = $this->fileService->getFileInAdapter('private', $filename);
        if (!is_file($file)) {
            $symfonyStyle->error('File not found ' . $filename);

            return Command::FAILURE;
        }

        $csv = new Csv();
        $csv->setDelimiter(';');
        $csv->setSheetIndex(0);

        $spreadsheet = $csv->load($file);
        $worksheet   = $spreadsheet->getActiveSheet();
        $dataJson    = $this->generateJson($worksheet);

        $progressBar = new ProgressBar($output, count($dataJson));
        $progressBar->start();
        foreach ($dataJson as $data) {
            if (empty($data['Imdb'])) {
                $progressBar->advance();
                continue;
            }

            $this->messageBus->dispatch(new AddSerieMessage($data));
            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->success('All series added');
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

    /**
     * @return list<array>
     */
    private function generateJson(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet): array
    {
        $dataJson    = [];
        $headers     = [];
        foreach ($worksheet->getRowIterator() as $i => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            if (1 === $i) {
                foreach ($cellIterator as $cell) {
                    $headers[] = trim((string) $cell->getValue());
                }

                continue;
            }

            $columns = [];
            foreach ($cellIterator as $cell) {
                $columns[] = trim((string) $cell->getValue());
            }

            $dataJson[] = array_combine($headers, $columns);
        }

        return $dataJson;
    }
}
