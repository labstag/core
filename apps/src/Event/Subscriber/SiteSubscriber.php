<?php

namespace Labstag\Event\Subscriber;

use Labstag\Entity\User;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SiteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
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
        $user = $this->getUser();
        if (!$user instanceof User) {
            return;
        }

        $locale = $user->getLanguage();
        $request->setLocale($locale);
    }

    private function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof \Symfony\Component\Security\Core\Authentication\Token\TokenInterface ? $token->getUser() : null;
    }
}
