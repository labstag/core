<?php

namespace Labstag\Controller;

use Labstag\Service\SiteService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/changepassword/{uid}', name: 'app_changepassword')]
    public function changePassword(mixed $uid): never
    {
        dd($uid);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, SiteService $siteService): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('admin');
        }

        return $this->render(
            '@EasyAdmin/page/login.html.twig',
            $this->getDataLogin($authenticationUtils, $siteService)
        );
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException();
    }

    /**
     * @return mixed[]
     */
    protected function getDataLogin(AuthenticationUtils $authenticationUtils, SiteService $siteService): array
    {
        $favicon      = $siteService->getFileFavicon();
        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $data         = $siteService->getConfiguration();

        $data = [
            // parameters usually defined in Symfony login forms
            'error'                   => $error,
            'last_username'           => $lastUsername,
            'translation_domain'      => 'admin',
            'page_title'              => $data->getName(),
            'csrf_token_intention'    => 'authenticate',
            'target_path'             => $this->generateUrl('admin'),
            'username_label'          => 'Your username',
            'password_label'          => 'Your password',
            'sign_in_label'           => 'Log in',
            'username_parameter'      => 'username',
            'password_parameter'      => 'password',
            'forgot_password_enabled' => false,
            'forgot_password_label'   => 'Forgot your password?',
            'remember_me_enabled'     => true,
            'remember_me_parameter'   => 'custom_remember_me_param',
            'remember_me_checked'     => true,
            'remember_me_label'       => 'Remember me',
        ];

        if (!is_null($favicon)) {
            $data['favicon_path'] = $favicon['public'];
        }

        return $data;
    }
}
