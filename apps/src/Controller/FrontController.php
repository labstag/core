<?php

namespace Labstag\Controller;

use Labstag\Service\ConfigurationService;
use Labstag\Service\FrontService;
use Labstag\Service\SiteService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatableMessage;

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

    // #[Route(path: '/login', name: 'app_login')]
    // public function login(
    //     AuthenticationUtils $authenticationUtils,
    //     ConfigurationService $configurationService,
    //     SiteService $siteService,
    // ): Response
    // {
    //     if ($this->getUser() instanceof UserInterface) {
    //         return $this->redirectToRoute('admin');
    //     }

    //     $response = $this->render(
    //         '@EasyAdmin/page/login.html.twig',
    //         $this->getDataLogin($authenticationUtils, $configurationService, $siteService)
    //     );

    //     $response->headers->set('X-Content-Type-Options', 'nosniff');
    //     $response->headers->set('X-Frame-Options', 'DENY');
    //     $response->headers->set('X-XSS-Protection', '1; mode=block');

    //     return $response;
    // }

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
}
