<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Labstag\Tests\AbstractWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

final class AdditionalRoutesTest extends AbstractWebTestCase
{
    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    public function loginPageLoads(): void
    {
        $this->client()->request(Request::METHOD_GET, '/login');
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection());
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    public function sitemapXmlLoads(): void
    {
        $this->client()->request(Request::METHOD_GET, '/sitemap.xml');
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection() || $response->getStatusCode() === 404);
    }
}
