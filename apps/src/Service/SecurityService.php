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
    public function __construct(
        protected Security $security,
        protected RequestStack $requestStack,
        protected FileService $fileService,
        protected BanIpRepository $banIpRepository,
        protected RedirectionRepository $redirectionRepository,
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
    )
    {
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
                'internetProtocol' => $this->getClientIp($request->server),
                'enable'           => true,
            ]
        );
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

        $agent = (string) $server->get('HTTP_USER_AGENT');
        if (empty($agent)) {
            $this->addBan($this->getClientIp($server));
            return;
        }

        if ($this->isForbiddenUrl($url)) {
            if (!is_null($this->security->getUser())) {
                $this->addBan($this->getClientIp($server));
            }

            return;
        }

        $referer = $request->headers->get('referer');
        $method = $server->get('REQUEST_METHOD');
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
        $httpErrorLogs->setAgent($agent);
        $httpErrorLogs->setHttpCode($httpCode);
        $httpErrorLogs->setInternetProtocol($this->getClientIp($server));
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

    private function getClientIp($server)
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($server->get($header))) {
                $ipList = explode(',', $server->get($header));
                // Si plusieurs IPs sont présentes (cas d'un proxy chainé)
                $internetProtocol = trim(end($ipList));
                // On prend la dernière IP de la liste (client réel)

                // Valider que c'est une IP valide (IPv4 ou IPv6)
                if (filter_var($internetProtocol, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $internetProtocol;
                }
            }
        }

        return '0.0.0.0';
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
        $file = $this->fileService->getFileInAdapter('private', 'disable.txt');
        $disable = explode("\n", file_get_contents($file));
        $find = false;
        foreach ($disable as $type) {
            if (str_contains((string) $url, $type)) {
                $find = true;

                break;
            }
        }

        return $find;
    }

    private function isForbiddenUrl($url): bool
    {
        $file = $this->fileService->getFileInAdapter('private', 'forbidden.txt');
        $forbidden = explode("\n", file_get_contents($file));
        $find = false;
        foreach ($forbidden as $type) {
            if (str_contains((string) $url, $type) || str_contains(strtolower((string) $url), strtolower($type))) {
                $find = true;

                break;
            }
        }

        return $find;
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
}
