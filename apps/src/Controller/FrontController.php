<?php

namespace Labstag\Controller;

use Carbon\Carbon;
use Labstag\Service\EtagCacheService;
use Labstag\Service\SitemapService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Labstag\Service\ViewResolverService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;

class FrontController extends AbstractController
{
    #[Route(
        '/{slug}/{page}',
        name: 'front',
        requirements: [
            'slug' => '.+?',
            // Le slug peut être vide
            'page' => '\d+',
        ],
        defaults: [
            'slug' => '',
            'page' => 1,
        ],
        priority: -1
    )]
    #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
    public function index(
        EtagCacheService $etagCacheService,
        ViewResolverService $viewResolverService,
        SlugService $slugService,
        SiteService $siteService,
        Request $request,
    ): Response
    {
        $entity = $slugService->getEntity();
        if (!is_object($entity)) {
            throw $this->createNotFoundException();
        }

        if (!$siteService->isEnable($entity)) {
            throw $this->createAccessDeniedException();
        }

        [
            $data,
            $view,
        ] = $viewResolverService->getDataViewByEntity($entity);

        // ETag & Last-Modified basés sur l'entité (si méthodes dispo)
        $cacheData = $etagCacheService->getCacheHeaders($entity);

        $response = $this->render($view, $data);
        $response->setEtag($cacheData['etag']);
        if ($cacheData['lastModified']) {
            $response->setLastModified($cacheData['lastModified']);
        }

        $response->setPublic();
        $response->setSharedMaxAge(3600);
        $response->setMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        // 304 Not Modified support
        if ($response->isNotModified($request)) {
            return $response;
            // Symfony ajuste automatiquement le contenu pour 304
        }

        return $response;
    }

    #[Route('/sitemap.css', name: 'sitemap.css', priority: 1)]
    public function sitemapCss(): Response
    {
        $response = new Response($this->renderView('sitemap/sitemap.css.twig'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }

    #[Route('/sitemap.js', name: 'sitemap.js', priority: 1)]
    public function sitemapJs(): Response
    {
        $response = new Response($this->renderView('sitemap/sitemap.js.twig'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    #[Route(
        '/sitemap.xml',
        name: 'sitemap.xml',
        priority: 1,
        defaults: ['_format' => 'xml']
    )]
    public function sitemapXml(SitemapService $sitemapService, ConfigurationService $configurationService): mixed
    {
        return $this->initCache()->get(
            'sitemap.xml',
            function (ItemInterface $item) use ($sitemapService, $configurationService): Response
            {
                $item->expiresAfter(3600);

                $sitemap = $sitemapService->getData(true);

                return $this->render(
                    'sitemap/sitemap.xml.twig',
                    [
                        'config'  => $configurationService->getConfiguration(),
                        'date'    => Carbon::now()->format('Y-m-d'),
                        'sitemap' => $sitemap,
                    ]
                );
            }
        );
    }

    #[Route('/sitemap.xsl', name: 'sitemap.xsl', priority: 1)]
    public function sitemapXsl(): Response
    {
        $response = new Response($this->renderView('sitemap/sitemap.xsl.twig'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    protected function initCache(): FilesystemAdapter
    {
        return new FilesystemAdapter('cache.app', 0, '../var');
    }
}
