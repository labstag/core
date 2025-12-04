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
        protected SiteService $siteService, private readonly FrontService $frontService,
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
    #[Cache(maxage: 3600, public: true, mustRevalidate: true)]
    public function index(): Response
    {
        return $this->frontService->showView();
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException();
    }

    #[Route('/sitemap.css', name: 'sitemap.css', priority: 1)]
    public function sitemapCss(): Response
    {
        return $this->frontService->getSitemapCss();
    }

    #[Route('/sitemap.js', name: 'sitemap.js', priority: 1)]
    public function sitemapJs(): Response
    {
        return $this->frontService->getSitemapJs();
    }

    #[Route('/sitemap.xml', name: 'sitemap.xml', defaults: ['_format' => 'xml'], priority: 1)]
    public function sitemapXml(): mixed
    {
        return $this->frontService->getSitemapXml();
    }

    #[Route('/sitemap.xsl', name: 'sitemap.xsl', priority: 1)]
    public function sitemapXsl(): Response
    {
        return $this->frontService->getSitemapXls();
    }
}
