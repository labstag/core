<?php

namespace Labstag\Replace;

class LinkLoginReplace extends ReplaceAbstract
{
    public function exec(): string
    {
        $configuration = $this->configurationService->getConfiguration();

        return $configuration->getUrl() . $this->router->generate('app_login', []);
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
