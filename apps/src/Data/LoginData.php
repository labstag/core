<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginData extends DataAbstract implements DataInterface
{
    #[\Override]
    public function scriptBefore(object $entity, Response $response): Response
    {
        unset($entity);
        $user = $this->security->getUser();
        if ($user instanceof UserInterface) {
            return new RedirectResponse(
                $this->router->generate(
                    'front',
                    ['slug' => '']
                )
            );
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }

    #[\Override]
    public function supportsScriptBefore(object $entity): bool
    {
        return $entity instanceof Page && $entity->getType() == PageEnum::LOGIN->value;
    }
}
