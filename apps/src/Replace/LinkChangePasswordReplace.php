<?php

namespace Labstag\Replace;

use Labstag\Replace\Abstract\ReplaceLib;

class LinkChangePasswordReplace extends ReplaceLib
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
        }

        $configuration = $this->configurationService->getConfiguration();
        $entity        = $this->data['user'];

        return $configuration->getUrl() . $this->router->generate(
            'app_changepassword',
            [
                'uid' => $entity->getId(),
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
