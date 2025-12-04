<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Labstag\Tests\AbstractWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

final class FrontControllerTest extends AbstractWebTestCase
{
    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('frontend')]
    public function simplehomepage(): void
    {
        $this->client()
            ->request(Request::METHOD_GET, '/');
        $this->assertTrue(
            $this->client()
                ->getResponse()
                ->isSuccessful()
            || $this->client()
                ->getResponse()
                ->isRedirection()
        );
    }
}
