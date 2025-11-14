<?php

namespace Labstag\Event\Subscriber;

use Labstag\Controller\FrontController;
use Labstag\Entity\BanIp;
use Labstag\Entity\User;
use Labstag\Service\SecurityService;
use Labstag\Service\ShortCodeService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SiteSubscriber
{
    public function __construct(
        protected ShortCodeService $shortCodeService,
        protected TokenStorageInterface $tokenStorage,
        protected SecurityService $securityService,
    )
    {
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onController(ResponseEvent $responseEvent): void
    {
        $response   = $responseEvent->getResponse();
        $request    = $responseEvent->getRequest();
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller)) {
            return;
        }

        $controller = explode('::', $controller);
        $controller = $controller[0] ?? '';
        if (FrontController::class != $controller) {
            return;
        }

        $content = $response->getContent();
        $content = $this->shortCodeService->changeContent($content);

        $response->setContent($content);

        $responseEvent->setResponse($response);
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $throwable = $exceptionEvent->getThrowable();
        if (!$throwable instanceof NotFoundHttpException) {
            return;
        }

        $statusCode = $throwable->getStatusCode();

        $this->securityService->set($statusCode);
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $redirect = $this->securityService->get();
        if ($redirect instanceof RedirectResponse) {
            $requestEvent->setResponse($redirect);

            return;
        }

        $banIp = $this->securityService->getBanIp();
        if ($banIp instanceof BanIp) {
            $requestEvent->setResponse(
                new Response(
                    sprintf(
                        'Your IP "%s" is banned<br />',
                        $this->securityService->getCurrentClientIp()
                    ) . $banIp->getReason(),
                    Response::HTTP_FORBIDDEN
                )
            );

            return;
        }

        $request = $requestEvent->getRequest();
        $user    = $this->getUser();
        if (!$user instanceof User) {
            return;
        }

        $locale = $user->getLanguage();
        $request->setLocale($locale);
    }

    private function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof TokenInterface ? $token->getUser() : null;
    }
}
