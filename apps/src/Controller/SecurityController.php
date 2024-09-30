<?php

namespace Labstag\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            '@EasyAdmin/page/login.html.twig',
            $this->getDataLogin($error, $lastUsername)
        );
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
            'username_parameter'      => 'my_custom_username_field',
            'password_parameter'      => 'my_custom_password_field',
            'forgot_password_enabled' => false,
            'forgot_password_label'   => 'Forgot your password?',
            'remember_me_enabled'     => true,
            'remember_me_parameter'   => 'custom_remember_me_param',
            'remember_me_checked'     => true,
            'remember_me_label'       => 'Remember me',
        ];
    }
}
