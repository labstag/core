<?php

namespace Labstag\Event\Subscriber;

use Override;
use Labstag\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SiteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage
    )
    {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();
        $user    = $this->getUser();
        if (!$user instanceof User) {
            return;
        }

        $locale = $user->getLanguage();
        $request->setLocale($locale);
    }

    private function getUser()
    {
        $token = $this->tokenStorage->getToken();

        return is_null($token) ? null : $token->getUser();
    }
}
