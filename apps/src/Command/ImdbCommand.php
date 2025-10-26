<?php

namespace Labstag\Command;

use Labstag\Api\TmdbApi;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Serie;
use Labstag\Message\MovieMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SerieRepository;
use Labstag\Service\MovieService;
use Labstag\Service\SerieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:imdb', description: 'Add a short description for your command',)]
class ImdbCommand extends Command
{
    public function __construct(
        protected SerieService $serieService,
        protected TmdbApi $tmdbApi,
        protected MovieService $movieService,
        protected MessageBusInterface $messageBus,
        protected SerieRepository $serieRepository,
        protected MovieRepository $movieRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $imdb         = $symfonyStyle->ask('Quel est le code IMDb ?');
        $data         = $this->tmdbApi->findByImdb($imdb);
        dump($data);
        if (is_null($data)) {
            $symfonyStyle->error("Le code IMDB n'est pas valide");

            return Command::INVALID;
        }
        if (isset($data['movie_results'][0]['id'])) {
            $imdb  = $data['movie_results'][0]['id'];
            $movie = $this->movieRepository->findOneBy(
                ['imdb' => $imdb]
            );
            if ($movie instanceof Movie) {
                $symfonyStyle->error('Le film existe déjà en base de données.');

                return Command::INVALID;
            }
            $movie = new Movie();
            $movie->setEnable(true);
            $movie->setAdult(false);
            $movie->setFile(false);
            $movie->setTitle($imdb);
            $movie->setImdb($imdb);
            $this->movieRepository->save($movie);
            $this->messageBus->dispatch(new MovieMessage($movie->getId()));
            $symfonyStyle->text(sprintf('Film %s ajouté en base de données.', $movie->getTitle()));
            return Command::SUCCESS;
        }

        if (isset($data['tv_results'][0]['id'])) {
            $serie = $this->serieRepository->findOneBy(
                ['imdb' => $imdb]
            );
            if ($serie instanceof Serie) {
                $symfonyStyle->error('La série existe déjà en base de données.');

                return Command::SUCCESS;
            }
            $serie = new Serie();
            $meta  = new Meta();
            $serie->setFile(false);
            $serie->setMeta($meta);
            $serie->setEnable(true);
            $serie->setAdult(false);
            $serie->setImdb($imdb);
            $serie->setTitle($imdb);
            $this->serieRepository->save($serie);
            $this->messageBus->dispatch(new SerieMessage($serie->getId()));
            $symfonyStyle->text(sprintf('Série %s ajoutée en base de données.', $serie->getTitle()));
            return Command::SUCCESS;
        }

        $symfonyStyle->error('Problème dans le retour API');

        return Command::FAILURE;
    }
}
