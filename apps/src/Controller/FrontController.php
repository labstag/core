<?php

namespace Labstag\Controller;

use Carbon\Carbon;
use Labstag\Service\ConfigurationService;
use Labstag\Service\EtagCacheService;
use Labstag\Service\SitemapService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Labstag\Service\ViewResolverService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Cache\ItemInterface;

class FrontController extends AbstractController
{
    public function __construct(
        protected EtagCacheService $etagCacheService,
        protected ViewResolverService $viewResolverService,
        protected SlugService $slugService,
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
    public function index(Request $request): Response
    {
        $entity = $this->slugService->getEntity();
        if (!is_object($entity)) {
            throw $this->createNotFoundException();
        }

        if (!$this->siteService->isEnable($entity)) {
            throw $this->createAccessDeniedException();
        }

        return $this->showContentEntity($entity, $request);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        ConfigurationService $configurationService,
        SiteService $siteService,
    ): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('admin');
        }

        $response = $this->render(
            '@EasyAdmin/page/login.html.twig',
            $this->getDataLogin($authenticationUtils, $configurationService, $siteService)
        );

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException();
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

    /**
     * @return mixed[]
     */
    protected function getDataLogin(
        AuthenticationUtils $authenticationUtils,
        ConfigurationService $configurationService,
        SiteService $siteService,
    ): array
    {
        $favicon      = $siteService->getFileFavicon();
        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $data         = $configurationService->getConfiguration();

        $data = [
            // parameters usually defined in Symfony login forms
            'error'                   => $error,
            'last_username'           => $lastUsername,
            'translation_domain'      => 'admin',
            'page_title'              => $data->getName(),
            'csrf_token_intention'    => 'authenticate',
            'target_path'             => $this->generateUrl('admin'),
            'username_label'          => new TranslatableMessage('Your username'),
            'password_label'          => new TranslatableMessage('Your password'),
            'sign_in_label'           => new TranslatableMessage('Log in'),
            'username_parameter'      => 'username',
            'password_parameter'      => 'password',
            'forgot_password_enabled' => false,
            'forgot_password_label'   => new TranslatableMessage('Forgot your password?'),
            'remember_me_enabled'     => true,
            'remember_me_parameter'   => 'remember_me',
            'remember_me_checked'     => true,
            'remember_me_label'       => new TranslatableMessage('Remember me'),
        ];

        if (!is_null($favicon)) {
            $data['favicon_path'] = $favicon['public'];
        }

        return $data;
    }

    protected function initCache(): FilesystemAdapter
    {
        return new FilesystemAdapter('cache.app', 0, '../var');
    }

    private function showContentEntity(?object $entity, Request $request): Response
    {
        $result       = $this->viewResolverService->getDataViewByEntity($entity);
        $view         = $result['view'];
        $templateData = $result['data'];

        // ETag & Last-Modified basés sur l'entité (si méthodes dispo)
        $cacheData = $this->etagCacheService->getCacheHeaders($entity);

        $response = $this->render($view, $templateData);
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
}
