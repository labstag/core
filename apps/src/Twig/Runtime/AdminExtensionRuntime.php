<?php

namespace Labstag\Twig\Runtime;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Labstag\Entity\Serie;
use Labstag\Service\Imdb\MovieService;
use Labstag\Service\Imdb\SagaService;
use Labstag\Service\Imdb\SerieService;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AdminExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
        private MovieService $movieService,
        private SerieService $serieService,
        private SagaService $sagaService,
        private AdminUrlGenerator $adminUrlGenerator,
        private TranslatorInterface $translator,
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
        return match (true) {
            $entity instanceof Movie => $this->movieService->recommandations($entity),
            $entity instanceof Serie => $this->serieService->recommandations($entity),
            $entity instanceof Saga  => $this->sagaService->recommandations($entity),
            default                  => [],
        };
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
}
