<?php

namespace Labstag\Lib;

use Labstag\Interface\ReplaceInterface;
use Labstag\Service\SiteService;
use Symfony\Component\Routing\RouterInterface;

abstract class ReplaceLib implements ReplaceInterface
{

    /**
     * @var mixed[]
     */
    protected array $data;

    public function __construct(
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
