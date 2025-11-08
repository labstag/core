<?php

namespace Labstag\Controller;

use Labstag\Service\FrontService;
use Labstag\Service\SiteService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class FrontController extends AbstractController
{
    public function __construct(
        protected SiteService $siteService,
    )
    {
    }

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
    public function index(FrontService $frontService): Response
    {
        return $frontService->showView();
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException();
    }

    #[Route('/sitemap.css', name: 'sitemap.css', priority: 1)]
    public function sitemapCss(FrontService $frontService): Response
    {
        return $frontService->getSitemapCss();
    }

    #[Route('/sitemap.js', name: 'sitemap.js', priority: 1)]
    public function sitemapJs(FrontService $frontService): Response
    {
        return $frontService->getSitemapJs();
    }

    #[Route(
        '/sitemap.xml',
        name: 'sitemap.xml',
        priority: 1,
        defaults: ['_format' => 'xml']
    )]
    public function sitemapXml(FrontService $frontService): mixed
    {
        return $frontService->getSitemapXml();
    }

    #[Route('/sitemap.xsl', name: 'sitemap.xsl', priority: 1)]
    public function sitemapXsl(FrontService $frontService): Response
    {
        return $frontService->getSitemapXls();
    }
}
