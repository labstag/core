<?php

namespace Labstag\Twig\Runtime;

use Labstag\Service\SiteService;
use Twig\Extension\RuntimeExtensionInterface;

class FrontExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected SiteService $siteService
    )
    {
        // Inject dependencies if needed
    }

    public function metatags($value)
    {
        // TODO
        unset($value);
        // ...
    }

    public function path($entity)
    {
        return $this->siteService->getSlugByEntity($entity);
    }

    public function title($value)
    {
        // TODO
        unset($value);

        return 'Welcome !';
    }
}
