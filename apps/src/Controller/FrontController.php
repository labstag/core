<?php

namespace Labstag\Controller;

use Labstag\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class FrontController extends AbstractController
{
    #[Route('{slug}{_</(?!/)>}', name: 'front', requirements: ['slug' => '.*'], defaults: ['slug' => '', '_' => ''], priority: -1)]
    public function index(
        $slug,
        SiteService $siteService
    ): Response
    {
        $entity = $siteService->getEntityBySlug($slug);
        if (!is_object($entity)) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            $siteService->getViewByEntity($entity),
            $siteService->getDataByEntity($entity)
        );
    }
}
