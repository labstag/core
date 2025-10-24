<?php

namespace Labstag\Command;

use Labstag\Entity\Meta;
use Labstag\Entity\Serie;
use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
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

    /**
     * @var array<string, mixed>
     */
    private array $imdbs = [];

    private int $update = 0;

    public function __construct(
        protected SerieRepository $serieRepository,
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

            $serie = $this->setSerie($data);
            $this->addOrUpdate($serie);

            $this->serieRepository->persist($serie);
            $this->serieRepository->flush();
            $this->messageBus->dispatch(new SerieMessage($serie->getId()));
            $progressBar->advance();
        }

        $this->serieRepository->flush();
        $progressBar->finish();
        $this->showOldsSeries($symfonyStyle);
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
    private function generateJson(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet,
    ): array
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

    private function getSerieByImdb(string $imdb): ?Serie
    {
        return $this->serieRepository->findOneBy(
            ['imdb' => $imdb]
        );
    }

    /**
     * @param mixed[] $data
     */
    private function setSerie(array $data): Serie
    {
        $imdb  = (string) $data['Imdb'];
        $serie = $this->getSerieByImdb($imdb);
        if (!$serie instanceof Serie) {
            $serie = new Serie();
            $meta  = new Meta();
            $serie->setMeta($meta);
            $serie->setEnable(true);
            $serie->setAdult(false);
            $serie->setImdb($imdb);
        }

        $tmdb       = (string) $data['tmdbId'];
        $title      = trim((string) $data['Title']);
        $serie->setTmdb($tmdb);
        $serie->setTitle($title);
        $serie->setFile(true);

        return $serie;
    }

    private function showOldsSeries(SymfonyStyle $symfonyStyle): void
    {
        $oldsSeries = $this->serieRepository->findSeriesNotInImdbList($this->imdbs);
        foreach ($oldsSeries as $oldSeries) {
            if ($oldSeries->isFile()) {
                $symfonyStyle->warning(
                    sprintf('Serie %s (%s) not in list', $oldSeries->getTitle(), $oldSeries->getImdb())
                );
            }
        }
    }
}
