<?php

namespace Labstag\Command;

use Labstag\Entity\Category;
use Labstag\Entity\Movie;
use Labstag\Repository\CategoryRepository;
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

#[AsCommand(name: 'labstag:movies-add', description: 'Add movies with movies.csv')]
class MovieAddCommand extends Command
{

    private int $add = 0;

    /**
     * @var Category[]
     */
    private array $categories = [];

    private int $update = 0;

    public function __construct(
        protected MovieRepository $movieRepository,
        protected FileService $fileService,
        protected CategoryRepository $categoryRepository,
    )
    {
        parent::__construct();
    }

    public function getCategory(string $value): Category
    {
        if (isset($this->categories[$value])) {
            return $this->categories[$value];
        }

        $category = $this->categoryRepository->findOneBy(
            [
                'type'  => 'movie',
                'title' => $value,
            ]
        );
        if ($category instanceof Category) {
            return $category;
        }

        $category = new Category();
        $category->setType('movie');
        $category->setTitle($value);

        $this->categoryRepository->save($category);
        $this->categories[$value] = $category;

        return $category;
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
        $filename = 'movies.csv';
        $file = $this->fileService->getFileInAdapter('private', $filename);
        if (!is_file($file)) {
            $symfonyStyle->error('File not found '.$filename);

            return Command::FAILURE;
        }

        $this->disableAll();
        $csv = new Csv();
        $csv->setDelimiter(';');
        $csv->setSheetIndex(0);

        $spreadsheet = $csv->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $dataJson = [];
        $headers = [];
        $counter = 0;
        foreach ($worksheet->getRowIterator() as $i => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            if ($i == 1) {
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

        $progressBar = new ProgressBar($output, count($dataJson));
        $progressBar->start();
        foreach ($dataJson as $data) {
            $movie = $this->setMovie($data);
            $this->addOrUpdate($movie);
            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
            $progressBar->advance();
        }

        $this->movieRepository->flush();
        $progressBar->finish();

        $symfonyStyle->success('All movie added');
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

    private function disableAll(): void
    {
        $movies = $this->movieRepository->findBy(
            ['enable' => true]
        );
        $counter = 0;
        foreach ($movies as $movie) {
            $movie->setEnable(false);
            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
        }

        $this->movieRepository->flush();
    }

    private function setSuffix($matches)
    {
        return $matches['3'] ?? null;
    }

    private function setEvaluation($matches)
    {
        return (float) isset($matches['1']) !== 0.0 ? $matches['1'] : null;
    }

    /**
     * @param mixed[] $data
     */
    private function setMovie(array $data): Movie
    {
        $imdb = str_pad((string) $data['ID IMDb'], 7, '0', STR_PAD_LEFT);
        $movie = $this->movieRepository->findOneBy(
            ['imdb' => $imdb]
        );

        $pattern = '/(\d+\.\d+)\s+\(([\d.]+)([KMB]?) votes\)/';
        preg_match($pattern, (string) $data['Evaluation IMDb'], $matches);
        $evaluation = $this->setEvaluation($matches);
        $suffix = $this->setSuffix($matches);
        $votes = $this->setVotes($suffix, $matches);

        if (!$movie instanceof Movie) {
            $movie = new Movie();
            $movie->setEnable(true);
            $movie->setImdb($imdb);
        }

        $year = (int) $data['Année'];
        $type = $data['Genre(s)'];
        $country = $data['Pays'];
        $color = ($data['Couleur'] == '<<Inconnu>>') ? null : $data['Couleur'];
        $trailer = empty($data['Bande-annonce']) ? null : $data['Bande-annonce'];
        $duration = empty($data['Durée']) ? null : $data['Durée'];
        $title = trim((string) $data['Titre']);
        $movie->setEvaluation($evaluation);
        $movie->setVotes($votes);
        $movie->setDuration($duration);
        $movie->setTrailer($trailer);
        $movie->setColor($color);
        $movie->setTitle($title);
        $movie->setYear(($year != 0) ? $year : null);
        $movie->setCountry(($country != '') ? $country : null);

        $categories = explode(',', (string) $type);
        $this->setCategories($movie, $categories);

        return $movie;
    }

    private function setVotes($suffix, $matches)
    {
        $votes = (float) isset($matches['2']) !== 0.0 ? $matches['1'] : null;
        switch ($suffix) {
            case 'K':
                $votes *= 1000;
                break;
            case 'M':
                $votes *= 1000000;
                break;
            case 'B':
                $votes *= 1000000000;
                break;
        }

        return $votes;
    }

    private function setCategories($movie, $categories)
    {
        $oldCategories = $movie->getCategories();
        foreach ($oldCategories as $oldCategory) {
            $movie->removeCategory($oldCategory);
        }

        foreach ($categories as $value) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }

            if ($value === '0') {
                continue;
            }

            $category = $this->getCategory($value);
            $movie->addCategory($category);
        }

    }
}
