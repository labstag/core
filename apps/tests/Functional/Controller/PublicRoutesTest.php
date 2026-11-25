<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Labstag\Tests\AbstractWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

final class PublicRoutesTest extends AbstractWebTestCase
{
    #[Test]
    #[Group('functional')]
    #[Group('public')]
    public function homepageLoads(): void
    {
        $this->client()->request(Request::METHOD_GET, '/');
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection());
    }

    #[Test]
    #[Group('functional')]
    #[Group('public')]
    public function sitemapLoads(): void
    {
        foreach (['/sitemap.xml', '/sitemap.css', '/sitemap.js', '/sitemap.xsl'] as $path) {
            $this->client()->request(Request::METHOD_GET, $path);
            $this->assertTrue($this->client()->getResponse()->isSuccessful(), $path);
        }
    }
}
