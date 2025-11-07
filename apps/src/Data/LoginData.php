<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Symfony\Component\HttpFoundation\Response;

class LoginData extends DataAbstract implements DataInterface
{
    #[\Override]
    public function scriptBefore(object $entity, Response $response): Response
    {
        unset($entity);
        dump('aa');

        return $response;
    }

    #[\Override]
    public function supportsScriptBefore(object $entity): bool
    {
        $page = $this->loginPage();

        return $page instanceof Page && $entity->getId() === $page->getId();
    }

    private function loginPage(): ?Page
    {
        $entityRepository = $this->entityManager->getRepository(Page::class);
        /** @var Page|null $page */
        $page = $entityRepository->findOneBy(
            [
                'type' => PageEnum::LOGIN->value,
            ]
        );

        return $page;
    }
}
