<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;
use Labstag\Tests\AbstractWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

final class AdminControllerTest extends AbstractWebTestCase
{
    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function dashboardLoads(): void
    {
        $this->client()->request(Request::METHOD_GET, '/admin');
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection() || Response::HTTP_FORBIDDEN === $response->getStatusCode());
    }
}
