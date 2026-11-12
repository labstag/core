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

        $params        = $this->slugService->forEntity($page);
        $params['uid'] = $entity->getId();

        return $configuration->getUrl() . $this->router->generate('front', $params);
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
