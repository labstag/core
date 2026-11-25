<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Configuration;
use Labstag\Repository\PostRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\SitemapService;
use Labstag\Service\SlugService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function getDataReturnsArray(): void
    {
        $config        = $this->createMock(ConfigurationService::class);
        $configuration = $this->createMock(Configuration::class);
        $configuration->method('isSitemapPosts')->willReturn(false);
        $configuration->method('isSitemapStory')->willReturn(false);
        $config->method('getConfiguration')->willReturn($configuration);

        $slug = $this->createMock(SlugService::class);
        $slug->method('getPageByType')->willReturn(null);

        $urlGen = $this->createStub(UrlGeneratorInterface::class);

        $repo = $this->createMock(PostRepository::class);
        $repo->method('getAllActivate')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $service = new SitemapService($config, $slug, $urlGen, $em);
        $result  = $service->getData(false);
        $this->assertIsArray($result);
    }
}
