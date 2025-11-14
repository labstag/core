<?php

namespace Labstag\Replace;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;

class LinkLoginReplace extends ReplaceAbstract
{
    public function exec(): string
    {
        $configuration    = $this->configurationService->getConfiguration();
        $entityRepository = $this->entityManager->getRepository(Page::class);
        $login            = $entityRepository->findOneBy(
            [
                'type' => PageEnum::LOGIN->value,
            ]
        );
        if (!$login instanceof Page) {
            return '#disableurl';
        }

        $slug = $this->slugService->forEntity($login);

        return $configuration->getUrl() . $this->router->generate(
            'front',
            ['slug' => $slug]
        );
    }

    public function getCode(): string
    {
        return 'link_login';
    }

    public function getTitle(): string
    {
        return 'Link login';
    }
}
