<?php

namespace Labstag\Replace;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Service\ConfigurationService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouterInterface;

#[AutoconfigureTag('labstag.replaces')]
abstract class ReplaceAbstract implements ReplaceInterface
{

    /**
     * @var mixed[]
     */
    protected array $data;

    public function __construct(
        protected ConfigurationService $configurationService,
        protected SiteService $siteService,
        protected SlugService $slugService,
        protected EntityManagerInterface $entityManager,
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
