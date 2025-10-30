<?php

namespace Labstag\Command;

use Labstag\Entity\Movie;
use Labstag\Message\AddMovieMessage;
use Labstag\Service\FileService;
use Override;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:movies:add', description: 'Add movies with movielist.csv')]
class MovieAddCommand extends Command
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

    protected function addOrUpdate(Movie $movie): void
    {
        if (is_null($movie->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $filename     = 'movielist.csv';
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
            $this->messageBus->dispatch(new AddMovieMessage($data));
            $progressBar->advance();
        }

        $progressBar->finish();

        $symfonyStyle->success('All movie added');

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
