<?php

namespace Labstag\Replace;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;

class LinkChangePasswordReplace extends ReplaceAbstract
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
        }

        $configuration = $this->configurationService->getConfiguration();
        $entity        = $this->data['user'];
        $id            = $entity->getId();
        if (is_null($id)) {
            return '#linkdisabled';
        }

        $entityRepository = $this->entityManager->getRepository(Page::class);

        $page = $entityRepository->findOneBy(
            [
                'type' => PageEnum::CHANGEPASSWORD->value,
            ]
        );
        if (!$page instanceof Page) {
            return '#linkdisabled';
        }

        $slug = $this->slugService->forEntity($page);

        return $configuration->getUrl() . $this->router->generate(
            'front',
            [
                'slug' => $slug,
                'uid'  => $entity->getId(),
            ]
        );
    }

    public function getCode(): string
    {
        return 'link_changepassword';
    }

    public function getTitle(): string
    {
        return 'Link to Change password';
    }
}
