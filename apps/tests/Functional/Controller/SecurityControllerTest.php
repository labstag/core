<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Labstag\Tests\AbstractWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

final class SecurityControllerTest extends AbstractWebTestCase
{
    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('security')]
    public function loginpageisaccessible(): void
    {
        $this->client()->request(Request::METHOD_GET, '/login');
        $this->assertResponseIsSuccessful();
    }
}
