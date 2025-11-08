<?php

namespace Labstag\Security;

use Labstag\Entity\Page;
use Labstag\Entity\User;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Repository\UserRepository;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private UserRepository $userRepository,
        protected SiteService $siteService,
        protected SlugService $slugService,
        protected PageRepository $pageRepository,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    #[Override]
    public function authenticate(Request $request): Passport
    {
        // Récupération des données du formulaire login
        $loginData = $request->request->all('login');
        $username  = $loginData['username'] ?? '';
        $password  = $loginData['password'] ?? '';
        $csrfToken = $loginData['_token'] ?? '';

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge(
                $username,
                function (string $username): User {
                    $user = $this->userRepository->findUserName($username);
                    if (!$user instanceof User) {
                        throw new CustomUserMessageAuthenticationException('Identifiant incorrect');
                    }

                    return $user;
                }
            ),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('login', $csrfToken),
                // Le nom doit correspondre au nom du formulaire
                new RememberMeBadge(),
            ]
        );
    }

    #[Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        unset($token);
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        $loginData  = $request->request->all('login');
        if (!$targetPath && isset($loginData['target_path'])) {
            $targetPath = $loginData['target_path'];
        }

        return new RedirectResponse($targetPath ?: $this->urlGenerator->generate('front'));
    }

    #[Override]
    protected function getLoginUrl(Request $request): string
    {
        unset($request);
        $login = $this->pageRepository->findOneBy(
            [
                'type' => PageEnum::LOGIN->value,
            ]
        );
        if (!$login instanceof Page) {
            return '#linkdisabled';
        }

        $slug = $this->slugService->forEntity($login);

        return $this->urlGenerator->generate(
            'front',
            ['slug' => $slug]
        );
    }
}
