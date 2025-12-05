<?php

namespace Labstag\Service;

use Carbon\Carbon;
use Labstag\Message\FileDeleteMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class FrontService extends AbstractController
{
    public function __construct(
        #[AutowireIterator('labstag.datas')]
        private readonly iterable $datas,
        protected CacheService $cacheService,
        protected MessageBusInterface $messageBus,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected SlugService $slugService,
        protected SitemapService $sitemapService,
        protected EtagCacheService $etagCacheService,
        protected ViewResolverService $viewResolverService,
        protected SiteService $siteService,
        protected RequestStack $requestStack,
    )
    {
    }

    public function getSitemapCss(): Response
    {
        $response = new Response($this->renderView('sitemap/sitemap.css.twig'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }

    public function getSitemapJs(): Response
    {
        $response = new Response($this->renderView('sitemap/sitemap.js.twig'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    public function getSitemapXls(): Response
    {
        $response = new Response($this->renderView('sitemap/sitemap.xsl.twig'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function getSitemapXml(): Response
    {
        $filePath      = $this->fileService->getFileInAdapter('public', 'sitemap.xml');
        if (!is_null($filePath)) {
            $content  = file_get_contents($filePath);

            $this->messageBus->dispatch(new FileDeleteMessage($filePath), [new DelayStamp(86_400_000)]);
            $response = new Response(false === $content ? '' : $content, Response::HTTP_OK);
            $response->headers->set('Content-Type', 'text/xml');

            return $response;
        }

        $content = $this->cacheService->get(
            'sitemap.xml',
            function (): Response
            {
                $sitemap = $this->sitemapService->getData(true);

                return $this->render(
                    'sitemap/sitemap.xml.twig',
                    [
                        'config'  => $this->configurationService->getConfiguration(),
                        'date'    => Carbon::now()->format('Y-m-d'),
                        'sitemap' => $sitemap,
                    ]
                );
            }
        );

        $this->fileService->saveFileInAdapter('public', 'sitemap.xml', $content->getContent());

        return $content;
    }

    public function showView(): Response
    {
        $entity = $this->slugService->getEntity();

        if (!is_object($entity)) {
            throw $this->createNotFoundException();
        }

        if (!$this->siteService->isEnable($entity)) {
            throw $this->createAccessDeniedException();
        }

        $response = $this->showContentEntity($entity);
        foreach ($this->datas as $data) {
            if ($data->supportsScriptBefore($entity)) {
                $response = $data->scriptBefore($entity, $response);
            }
        }

        return $response;
    }

    private function showContentEntity(?object $entity): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $result  = $this->viewResolverService->getDataViewByEntity($entity);

        // ETag & Last-Modified basés sur l'entité (si méthodes dispo)
        $cacheData = $this->etagCacheService->getCacheHeaders($entity);

        $response = $this->render($result['view'], $result['data']);
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
        }

        return $response;
    }
}
