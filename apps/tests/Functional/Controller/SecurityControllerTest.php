<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;
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
        $this->client()->request(Request::METHOD_GET, '/connexion');
        $this->assertResponseIsSuccessful();
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('security')]
    public function loginPostInvalid(): void
    {
        $this->client()->request(
            Request::METHOD_POST,
            '/connexion',
            [
                'login' => [
                    'username' => 'nope@example.com',
                    'password' => 'wrong',
                ],
            ]
        );
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isRedirection() || $response->isSuccessful());
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('security')]
    public function loginRouteLoads(): void
    {
        $this->client()->request(Request::METHOD_GET, '/login');
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection() || Response::HTTP_NOT_FOUND === $response->getStatusCode());
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('security')]
    public function logoutRouteLoads(): void
    {
        $this->client()->request(Request::METHOD_GET, '/logout');
        $response = $this->client()->getResponse();
        $this->assertTrue($response->isRedirection() || Response::HTTP_NOT_FOUND === $response->getStatusCode() || $response->isSuccessful());
    }
}
