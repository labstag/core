<?php

namespace Labstag\Command;

use Labstag\Entity\Category;
use Labstag\Entity\Movie;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\MovieRepository;
use NumberFormatter;
use Override;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:movies-add',
    description: 'Add movies with movies.csv',
)]
class MovieAddCommand extends Command
{

    private int $add = 0;

    private array $categories = [];

    private int $update = 0;

    public function __construct(
        protected MovieRepository $movieRepository,
        protected CategoryRepository $categoryRepository
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

    protected function addOrUpdate($entity)
    {
        if (is_null($entity->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $file         = getcwd().'/movies.csv';
        if (!is_file($file)) {
            $symfonyStyle->error('File not found');

            return Command::FAILURE;
        }

        $this->disableAll();
        $csv = new Csv();
        $csv->setDelimiter(';');
        $csv->setSheetIndex(0);

        $spreadsheet = $csv->load($file);
        $worksheet   = $spreadsheet->getActiveSheet();
        $dataJson    = [];
        $headers     = [];
        $counter     = 0;
        foreach ($worksheet->getRowIterator() as $i => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            if (1 == $i) {
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

        $progressBar = new ProgressBar($output, is_countable($dataJson) ? count($dataJson) : 0);
        $progressBar->start();
        foreach ($dataJson as $data) {
            $movie = $this->setStar($data);
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

    private function disableAll()
    {
        $movies = $this->movieRepository->findBy(
            ['enable' => true]
        );
        $counter = 0;
        foreach ($movies as $row) {
            $row->setEnable(false);
            ++$counter;

            $this->movieRepository->persist($row);
            $this->movieRepository->flush($counter);
        }

        $this->movieRepository->flush();
    }

    private function setStar($data)
    {
        $imdb  = str_pad((string) $data['ID IMDb'], 7, '0', STR_PAD_LEFT);
        $movie = $this->movieRepository->findOneBy(
            ['imdb' => $imdb]
        );

        if (!$movie instanceof Movie) {
            $movie = new Movie();
            $movie->setImdb($imdb);
        }

        $year    = (int) $data['Année'];
        $type    = $data['Genre(s)'];
        $country = $data['Pays'];
        $movie->setEnable(true);
        $movie->setTitle($data['Titre']);
        $movie->setYear((0 != $year) ? $year : null);
        $movie->setCountry(('' != $country) ? $country : null);

        $oldCategories = $movie->getCategories();
        foreach ($oldCategories as $oldCategory) {
            $movie->removeCategory($oldCategory);
        }

        $categories = explode(',', (string) $type);
        foreach ($categories as $value) {
            $value = trim($value);
            if ('' === $value || '0' === $value) {
                continue;
            }

            $category = $this->getCategory($value);
            $movie->addCategory($category);
        }

        return $movie;
    }
}
