<?php

namespace Labstag\Service;

use Labstag\Entity\BanIp;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Entity\Redirection;
use Labstag\Entity\User;
use Labstag\Repository\BanIpRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Repository\RedirectionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class SecurityService
{

    protected array $disable = [
        '/build',
        '/media',
    ];

    protected array $forbidden = [
        '.git',
        '.well-known',
        'wordpress',
        'mysql',
        'phpmyadmin',
        'wp-includes',
        'shopdb',
        'wp-',
        'atomlib.php',
        'enhancecp',
        'nmaplowercheck',
        'vendor',
    ];

    public function __construct(
        protected Security $security,
        protected RequestStack $requestStack,
        protected BanIpRepository $banIpRepository,
        protected RedirectionRepository $redirectionRepository,
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
    )
    {
    }

    public function get(): ?RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        if (is_null($request)) {
            return null;
        }

        $pathinfo = $request->getPathInfo();
        $slug = '/' . $request->attributes->get('slug');
        if ($slug !== $pathinfo) {
            $pathinfo = $slug;
        }

        $redirections = $this->getRedirections(false);
        if (count($redirections) == 0) {
            return null;
        }

        $redirect = $this->testRedirect($pathinfo, $redirections);
        if (is_null($redirect)) {
            $redirections = $this->getRedirections(true);
            $redirect = $this->testRedirectRegex($pathinfo, $redirections);
        }

        return $redirect;
    }

    public function getBanIp(): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser();
        if (!is_null($user)) {
            return null;
        }

        return $this->banIpRepository->findOneBy(
            [
                'internetProtocol' => $request->server->get('REMOTE_ADDR'),
                'enable'           => true,
            ]
        );
    }

    private function testRedirectRegex(string $pathinfo, array $redirections): ?RedirectResponse
    {
        $redirect = null;
        foreach ($redirections as $redirection) {
            if (preg_match($redirection->getSource(), $pathinfo)) {
                $redirect = $this->setRedirectResponse($redirection);

                break;
            }
        }

        return $redirect;
    }

    private function setRedirectResponse(Redirection $redirection): RedirectResponse
    {
        $redirection->incrementLastCount();
        $this->redirectionRepository->save($redirection);

        return new RedirectResponse($redirection->getDestination(), $redirection->getActionCode());
    }

    private function testRedirect(string $pathinfo, array $redirections): ?RedirectResponse
    {
        $redirect = null;
        foreach ($redirections as $redirection) {
            if ($redirection->getSource() == $pathinfo) {
                $redirect = $this->setRedirectResponse($redirection);

                break;
            }
        }

        return $redirect;
    }

    private function getRedirections(bool $regex): array
    {
        return $this->redirectionRepository->findBy(
            [
                'enable' => true,
                'regex'  => $regex,
            ],
            ['position' => 'ASC']
        );
    }

    private function isDisableUrl($url): bool
    {
        $find = false;
        foreach ($this->disable as $type) {
            if (str_contains((string) $url, (string) $type)) {
                $find = true;

                break;
            }
        }

        return $find;
    }

    private function isForbiddenUrl($url): bool
    {
        $find = false;
        foreach ($this->forbidden as $type) {
            if (str_contains((string) $url, (string) $type)) {
                $find = true;

                break;
            }
        }

        return $find;
    }

    public function set($httpCode = 404): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (is_null($request)) {
            return;
        }

        $server = $request->server;
        $httpErrorLogs = new HttpErrorLogs();
        $domain = $server->get('REQUEST_SCHEME') . '://' . $server->get('SERVER_NAME');
        $url = $server->get('REQUEST_URI');
        if ($this->isDisableUrl($url)) {
            return;
        }

        if ($this->isForbiddenUrl($url)) {
            if (!is_null($this->security->getUser())) {
                $this->addBan($server->get('REMOTE_ADDR'));
            }

            return;
        }

        $referer = $request->headers->get('referer');
        $method = $server->get('requestMethod');
        $data = $this->httpErrorLogsRepository->findBy(
            [
                'domain'        => $domain,
                'url'           => $url,
                'referer'       => $referer,
                'httpCode'      => $httpCode,
                'requestMethod' => $method,
            ]
        );

        if (count($data) != 0) {
            return;
        }

        $user = $this->security->getUser();
        $user = ($user instanceof User) ? $user : null;

        $httpErrorLogs->setRefUser($user);
        $httpErrorLogs->setDomain($domain);
        $httpErrorLogs->setUrl($url);
        $httpErrorLogs->setAgent((string) $server->get('HTTP_USER_AGENT'));
        $httpErrorLogs->setHttpCode($httpCode);
        $httpErrorLogs->setInternetProtocol($server->get('REMOTE_ADDR'));
        if (!is_null($referer)) {
            $httpErrorLogs->setReferer($referer);
        }

        $httpErrorLogs->setRequestData(
            [
                'get'  => $request->query->all(),
                'post' => $request->request->all(),
            ]
        );
        $httpErrorLogs->setRequestMethod($method);

        $this->httpErrorLogsRepository->save($httpErrorLogs);
    }

    public function addBan(string $internetProtocol): void
    {
        $banIp = $this->banIpRepository->findOneBy(
            ['internetProtocol' => $internetProtocol]
        );
        if ($banIp instanceof BanIp) {
            return;
        }

        $banIp = new BanIp();
        $banIp->setInternetProtocol($internetProtocol);
        $banIp->setEnable(true);

        $this->banIpRepository->save($banIp);
    }
}
