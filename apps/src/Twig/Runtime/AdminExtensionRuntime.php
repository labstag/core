<?php

namespace Labstag\Twig\Runtime;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\Serie;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AdminExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
        private AdminUrlGenerator $adminUrlGenerator,
        protected TranslatorInterface $translator,
        protected TheMovieDbApi $theMovieDbApi,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    public function name(object $entity): string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $entity::class || $entity instanceof $entityClass) {
                $crud = $controller->configureCrud(Crud::new());

                return $this->translator->trans($crud->getAsDto()->getEntityLabelInSingular());
            }
        }

        return '';
    }

    public function recommandations(object $entity): array
    {
        $recommandations     = [];
        $jsonRecommandations = match (true) {
            $entity instanceof Movie => $this->theMovieDbApi->getDetailsMovie($entity),
            $entity instanceof Serie => $this->theMovieDbApi->getDetailsSerie($entity),
            default                  => null,
        };

        if (!isset($jsonRecommandations['recommandations'])) {
            return $recommandations;
        }

        $this->entityManager->getRepository($entity::class);
        foreach ($jsonRecommandations['recommandations']['results'] as $recommandation) {
            $recommandation = $this->setRecommandation($recommandation, $entity);
            if (!is_array($recommandation)) {
                continue;
            }

            $recommandations[] = $recommandation;
        }

        $titleKey = $entity instanceof Movie ? 'title' : 'name';
        usort($recommandations, fn (array $a, array $b): int => strcasecmp($a[$titleKey] ?? '', $b[$titleKey] ?? ''));

        return $recommandations;
    }

    public function url(string $type, object $entity): string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $entity::class || $entity instanceof $entityClass) {
                $url = $this->adminUrlGenerator->setController($controller::class);
                $url->setAction($type);
                $url->setEntityId($entity->getId());

                return $url->generateUrl();
            }
        }

        return '';
    }

    private function setRecommandation(array $recommandation, object $entity): ?array
    {
        $entityRepository = $this->entityManager->getRepository($entity::class);
        $tmdb             = $recommandation['id'];
        $item             = $entityRepository->findOneBy(
            ['tmdb' => $tmdb]
        );
        if (is_object($item)) {
            return null;
        }

        $recommandation['poster_path'] = $this->theMovieDbApi->images()->getPosterUrl(
            $recommandation['poster_path'] ?? ''
        );
        $recommandation['backdrop_path'] = $this->theMovieDbApi->images()->getBackdropUrl(
            $recommandation['backdrop_path'] ?? ''
        );
        $recommandation['links'] = $entity instanceof Movie ? 'https://www.themoviedb.org/movie/' . $recommandation['id'] : 'https://www.themoviedb.org/tv/' . $recommandation['id'];

        return $recommandation;
    }
}
