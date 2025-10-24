<?php

namespace Labstag\Command;

use Labstag\Entity\Movie;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
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

#[AsCommand(name: 'labstag:movies:add', description: 'Add movies with movielist.csv')]
class MovieAddCommand extends Command
{

    private int $add = 0;

    /**
     * @var array<string, mixed>
     */
    private array $imdbs = [];

    private int $update = 0;

    public function __construct(
        protected MovieRepository $movieRepository,
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
        $counter     = 0;

        $progressBar = new ProgressBar($output, count($dataJson));
        $progressBar->start();
        foreach ($dataJson as $data) {
            $movie = $this->setMovie($data);
            $this->addOrUpdate($movie);

            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
            $this->messageBus->dispatch(new MovieMessage($movie->getId()));
            $progressBar->advance();
        }

        $this->movieRepository->flush();
        $progressBar->finish();
        $this->showoldsMovies($symfonyStyle);

        $symfonyStyle->success('All movie added');
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

    private function getMovieByImdb(string $imdb): ?Movie
    {
        return $this->movieRepository->findOneBy(
            ['imdb' => $imdb]
        );
    }

    /**
     * @param mixed[] $data
     */
    private function setMovie(array $data): Movie
    {
        $imdb  = (string) $data['ID IMDb'];
        $movie = $this->getMovieByImdb($imdb);
        if (!$movie instanceof Movie) {
            $movie = new Movie();
            $movie->setEnable(true);
            $movie->setAdult(false);
            $movie->setImdb($imdb);
        }

        $tmdb       = (string) $data['ID TMDB'];
        $duration   = empty($data['Durée']) ? null : (int) $data['Durée'];
        $title      = trim((string) $data['Titre']);
        $movie->setTmdb($tmdb);
        $movie->setDuration($duration);
        $movie->setTitle($title);
        $movie->setFile(true);

        return $movie;
    }

    private function showoldsMovies(SymfonyStyle $symfonyStyle): void
    {
        $oldsMovies = $this->movieRepository->findMoviesNotInImdbList($this->imdbs);
        foreach ($oldsMovies as $oldMovie) {
            if ($oldMovie->isFile()) {
                $symfonyStyle->warning(
                    sprintf('Movie %s (%s) not in list', $oldMovie->getTitle(), $oldMovie->getImdb())
                );
            }
        }
    }
}
