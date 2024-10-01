<?php

namespace Labstag\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            '@EasyAdmin/page/login.html.twig',
            $this->getDataLogin($error, $lastUsername)
        );
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    protected function getDataLogin($error, $lastUsername): array
    {
        return [
            // parameters usually defined in Symfony login forms
            'error'                   => $error,
            'last_username'           => $lastUsername,
            'translation_domain'      => 'admin',
            'favicon_path'            => '/favicon-admin.svg',
            'page_title'              => 'ACME login',
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
    }
}
