<?php

namespace Labstag\Replace;

class LinkChangePasswordReplace extends ReplaceAbstract
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
        }

        $configuration = $this->configurationService->getConfiguration();
        $entity        = $this->data['user'];
        $id = $entity->getId();
        if (is_null($id)) {
            return '#linkdisabled';
        }


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
