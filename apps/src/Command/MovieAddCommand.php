<?php

namespace Labstag\Command;

use Labstag\Entity\Category;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Labstag\Entity\Tag;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;
use Labstag\Repository\TagRepository;
use Labstag\Service\FileService;
use Labstag\Service\MovieService;
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

    /**
     * @var Tag[]
     */
    private array $tags = [];

    /**
     * @var Saga[]
     */
    private array $sagas = [];

    private array $imdbs = [];

    private int $update = 0;

    public function __construct(
        protected MovieRepository $movieRepository,
        protected MovieService $movieService,
        protected FileService $fileService,
        protected CategoryRepository $categoryRepository,
        protected TagRepository $tagRepository,
        protected SagaRepository $sagaRepository,
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
            $this->categories[$value] = $category;

            return $category;
        }

        $category = new Category();
        $category->setType('movie');
        $category->setTitle($value);

        $this->categoryRepository->save($category);
        $this->categories[$value] = $category;

        return $category;
    }

    public function getTag(string $value): Tag
    {
        if (isset($this->tags[$value])) {
            return $this->tags[$value];
        }

        $tag = $this->tagRepository->findOneBy(
            [
                'type'  => 'movie',
                'title' => $value,
            ]
        );
        if ($tag instanceof Tag) {
            $this->tags[$value] = $tag;

            return $tag;
        }

        $tag = new Tag();
        $tag->setType('movie');
        $tag->setTitle($value);

        $this->tagRepository->save($tag);
        $this->tags[$value] = $tag;

        return $tag;
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
        $filename     = 'movies.csv';
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

        $progressBar = new ProgressBar($output, count($dataJson));
        $progressBar->start();
        foreach ($dataJson as $data) {
            $movie = $this->setMovie($data);
            if ($movie instanceof Movie) {
                $this->movieService->update($movie);
                $this->addOrUpdate($movie);
            }

            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
            $progressBar->advance();
        }

        $this->movieRepository->flush();
        $progressBar->finish();

        $oldsMovies = $this->movieRepository->findMoviesNotInImdbList($this->imdbs);
        foreach ($oldsMovies as $oldMovie) {
            $symfonyStyle->warning(sprintf('Movie %s (%s) not in list', $oldMovie->getTitle(), $oldMovie->getImdb()));
        }

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

    private function setTags(Movie $movie, $tags): void
    {
        $oldTags = $movie->getTags();
        foreach ($oldTags as $oldTag) {
            $movie->removeTag($oldTag);
        }

        foreach ($tags as $value) {
            $value = trim((string) $value);
            if ('' === $value) {
                continue;
            }

            if ('0' === $value) {
                continue;
            }

            $tag = $this->getTag($value);
            $movie->addTag($tag);
        }
    }

    private function setCategories(Movie $movie, $categories): void
    {
        $oldCategories = $movie->getCategories();
        foreach ($oldCategories as $oldCategory) {
            $movie->removeCategory($oldCategory);
        }

        foreach ($categories as $value) {
            $value = trim((string) $value);
            if ('' === $value) {
                continue;
            }

            if ('0' === $value) {
                continue;
            }

            $category = $this->getCategory($value);
            $movie->addCategory($category);
        }
    }

    private function getMovieByImdb(string $imdb): ?Movie
    {
        $searchs[]['imdb'] = $imdb;
        if (str_starts_with($imdb, 'tt')) {
            $this->imdbs[]     = $imdb;
            $searchs[]['imdb'] = str_pad(substr($imdb, 2), 7, '0', STR_PAD_LEFT);
        }
        if (!str_starts_with($imdb, 'tt')) {
            $this->imdbs[] = 'tt' . str_pad($imdb, 7, '0', STR_PAD_LEFT);
        }

        foreach ($searchs as $search) {
            $movie = $this->movieRepository->findOneBy($search);
            if ($movie instanceof Movie) {
                return $movie;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $data
     */
    private function setMovie(array $data): ?Movie
    {
        $imdb  = (string) $data['ID IMDb'];
        $movie = $this->getMovieByImdb($imdb);
        if (!$movie instanceof Movie) {
            $movie = new Movie();
            $movie->setEnable(true);
            $movie->setImdb($imdb);
        }

        $year       = (int) $data['Année'];
        $categories = explode(',', (string) $data['Genre(s)']);
        $tags       = explode(',', (string) $data['Tags']);
        $country    = $data['Pays'];
        $duration   = empty($data['Durée']) ? null : (int) $data['Durée'];
        $saga       = empty($data['Saga']) ? null : $data['Saga'];
        $title      = trim((string) $data['Titre']);
        $movie->setDuration($duration);
        $movie->setTitle($title);
        $movie->setYear((0 != $year) ? $year : null);
        $movie->setCountry(('' != $country) ? $country : null);
        $this->setSaga($movie, $saga);

        $this->setCategories($movie, $categories);
        $this->setTags($movie, $tags);

        return $movie;
    }

    private function getSaga(string $value): Saga
    {
        if (isset($this->sagas[$value])) {
            return $this->sagas[$value];
        }

        $saga = $this->sagaRepository->findOneBy(
            ['title' => $value]
        );
        if ($saga instanceof Saga) {
            $this->sagas[$value] = $saga;

            return $saga;
        }

        $saga = new Saga();
        $saga->setTitle($value);

        $this->sagaRepository->save($saga);
        $this->sagas[$value] = $saga;

        return $saga;
    }

    private function setSaga(Movie $movie, $saga): void
    {
        if (is_null($saga) || '' === $saga) {
            return;
        }

        $saga = trim(str_replace('- Saga', '', $saga));

        $saga = $this->getSaga($saga);

        $movie->setSaga($saga);
    }
}
