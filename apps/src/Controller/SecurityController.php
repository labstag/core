<?php

namespace Labstag\Controller;

use Labstag\Service\ConfigurationService;
use Labstag\Service\SiteService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatableMessage;

class SecurityController extends AbstractController
{
    #[Route(path: '/changepassword/{uid}', name: 'app_changepassword')]
    public function changePassword(mixed $uid): never
    {
        dd($uid);
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
