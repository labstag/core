<?php

namespace Labstag\Controller;

use Carbon\Carbon;
use Labstag\Service\SitemapService;
use Labstag\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/')]
class FrontController extends AbstractController
{
    #[Route('{slug}{_</(?!/)>}', name: 'front', requirements: ['slug' => '.*'], defaults: ['slug' => '', '_' => ''], priority: -1)]
    public function index(
        SiteService $siteService
    ): Response
    {
        $entity = $siteService->getEntity();
        if (!is_object($entity)) {
            throw $this->createNotFoundException();
        }

        if (!$siteService->isEnable($entity)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render(
            $siteService->getViewByEntity($entity),
            $siteService->getDataByEntity($entity)
        );
    }

    #[Route('sitemap.css', name: 'sitemap.css', priority: 1)]
    public function sitemapCss(): Response
    {
        $response = new Response(
            $this->renderView('sitemap/sitemap.css.twig'),
            Response::HTTP_OK
        );
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }

    #[Route('sitemap.js', name: 'sitemap.js', priority: 1)]
    public function sitemapJs(): Response
    {
        $response = new Response(
            $this->renderView('sitemap/sitemap.js.twig'),
            Response::HTTP_OK
        );
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    #[Route('sitemap.xml', name: 'sitemap.xml', priority: 1, defaults: ['_format' => 'xml'])]
    public function sitemapXml(
        SitemapService $sitemapService
    ): mixed
    {
        return $this->initCache()->get(
            'sitemap.xml',
            function (ItemInterface $item) use ($sitemapService): Response
            {
                $item->expiresAfter(3600);

                $sitemap = $sitemapService->getData(1);

                return $this->render(
                    'sitemap/sitemap.xml.twig',
                    [
                        'date'    => Carbon::now()->format('Y-m-d'),
                        'sitemap' => $sitemap,
                    ]
                );
            }
        );
    }

    #[Route('sitemap.xsl', name: 'sitemap.xsl', priority: 1)]
    public function sitemapXsl(): Response
    {
        $response = new Response(
            $this->renderView('sitemap/sitemap.xsl.twig'),
            Response::HTTP_OK
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    protected function initCache(): FilesystemAdapter
    {
        return new FilesystemAdapter(
            'cache.app',
            0,
            '../var'
        );
    }
}
