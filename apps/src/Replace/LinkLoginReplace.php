<?php

namespace Labstag\Replace;

use Labstag\Lib\ReplaceLib;

class LinkLoginReplace extends ReplaceLib
{
    public function exec(): string
    {
        $configuration = $this->siteService->getConfiguration();

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
