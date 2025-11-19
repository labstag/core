<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Labstag\Message\SagaMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;
use Labstag\Service\FileService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\MessageBusInterface;

final class SagaService
{

    private ?array $jsonTmdb = null;

    public function __construct(
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
        private LoggerInterface $logger,
        private AdminUrlGenerator $adminUrlGenerator,
        private MessageBusInterface $messageBus,
        private SagaRepository $sagaRepository,
        private MovieRepository $movieRepository,
        private FileService $fileService,
        private EntityManagerInterface $entityManager,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getAllRecommandations(): array
    {
        $rows = $this->entityManager->getConnection()->fetchAllAssociative('SELECT json FROM saga');

        $results         = array_column($rows, 'json');
        $recommandations = [];
        foreach ($results as $result) {
            $data            = json_decode((string) $result, true);
            $recommandations = $this->setJsonRecommandations($data, $recommandations);
        }

        return $recommandations;
    }

    public function getSaga(array $data): Saga
    {
        $saga = $this->sagaRepository->findOneBy(
            [
                'tmdb' => $data['id'],
            ]
        );
        if (!$saga instanceof Saga) {
            $saga = new Saga();
            $saga->setEnable(true);
            $saga->setTitle($this->setName($data));
            $saga->setTmdb($data['id']);
            $this->sagaRepository->save($saga);
            $this->messageBus->dispatch(new SagaMessage($saga->getId()));
        }

        return $saga;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSagaForForm(): array
    {
        $data  = $this->sagaRepository->findAllByTypeMovieEnable();
        $sagas = [];
        foreach ($data as $saga) {
            $movies = $saga->getMovies();
            if (1 === count($movies)) {
                continue;
            }

            $sagas[$saga->getTitle()] = $saga->getSlug();
        }

        return $sagas;
    }

    public function recommandations(Saga $saga, array $recommandations = []): array
    {
        $jsonRecommandations = $this->theMovieDbApi->getDetailsSaga($saga);

        return $this->setJsonRecommandations($jsonRecommandations, $recommandations);
    }

    public function update(Saga $saga): bool
    {
        $details  = $this->theMovieDbApi->getDetailsSaga($saga);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $this->sagaRepository->delete($saga);
            $this->logger->error('Saga not found TMDB id ' . $saga->getTmdb());

            return false;
        }

        $statuses = [
            $this->updateSaga($saga, $details),
            $this->updateImageSaga($saga, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function getAllJsonTmdb(): array
    {
        if (!is_null($this->jsonTmdb)) {
            return $this->jsonTmdb;
        }

        $this->jsonTmdb = $this->movieRepository->getAllJsonTmdb();

        return $this->jsonTmdb;
    }

    private function setJsonRecommandations(?array $json, array $recommandations = []): array
    {
        if (!is_array($json) || !isset($json['tmdb']['parts'])) {
            return $recommandations;
        }

        foreach ($json['tmdb']['parts'] as $recommandation) {
            $tmdb              = $recommandation['id'];
            if (isset($recommandations[$tmdb])) {
                continue;
            }

            $recommandation = $this->setRecommandation($recommandation);
            if (!is_array($recommandation)) {
                continue;
            }

            $recommandations[$tmdb] = $recommandation;
        }

        return $recommandations;
    }

    private function setName(array $data): string
    {
        $name = trim(str_replace('- Saga', '', $data['tmdb']['name']));

        return trim(str_replace('- Saga', '', $name));
    }

    private function setRecommandation(array $recommandation): ?array
    {
        $tmdbs            = $this->getAllJsonTmdb();
        $tmdb             = $recommandation['id'];
        if (in_array($tmdb, $tmdbs)) {
            return null;
        }

        $recommandation['poster_path'] = $this->theMovieDbApi->images()->getPosterUrl(
            $recommandation['poster_path'] ?? ''
        );
        $recommandation['backdrop_path'] = $this->theMovieDbApi->images()->getBackdropUrl(
            $recommandation['backdrop_path'] ?? ''
        );
        $recommandation['links'] = 'https://www.themoviedb.org/movie/' . $recommandation['id'];
        $recommandation['add']   = $this->urlAddWithTmdb('addWithTmdb', $recommandation);

        $recommandation['date'] = new DateTime($recommandation['release_date']);
        if ($recommandation['date'] > new DateTime()) {
            return null;
        }

        return $recommandation;
    }

    private function updateImageSaga(Saga $saga, array $data): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($data['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' !== (string) $saga->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $saga, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateSaga(Saga $saga, array $details): bool
    {
        $saga->setTitle($this->setName($details));
        $saga->setDescription($details['tmdb']['overview']);
        $saga->setJson($details);

        return true;
    }

    private function urlAddWithTmdb(string $type, array $data): string
    {
        $movie = new Movie();
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $movie::class || $movie instanceof $entityClass) {
                $url = $this->adminUrlGenerator->setController($controller::class);
                $url->set('name', $data['title'] ?? $data['name']);
                $url->set('tmdb', $data['id']);
                $url->setAction($type);

                return str_replace('http://localhost', '', $url->generateUrl());
            }
        }

        return '';
    }
}
