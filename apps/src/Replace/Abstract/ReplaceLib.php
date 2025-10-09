<?php

namespace Labstag\Replace\Abstract;

use Labstag\Interface\ReplaceInterface;
use Labstag\Service\ConfigurationService;
use Labstag\Service\SiteService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouterInterface;

#[AutoconfigureTag('labstag.replaces')]
abstract class ReplaceLib implements ReplaceInterface
{

    /**
     * @var mixed[]
     */
    protected array $data;

    public function __construct(
        protected ConfigurationService $configurationService,
        protected SiteService $siteService,
        protected RouterInterface $router,
    )
    {
    }

    /**
     * @param mixed[] $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
