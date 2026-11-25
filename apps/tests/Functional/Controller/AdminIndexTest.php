<?php

declare(strict_types=1);

namespace Labstag\Tests\Functional\Controller;

use Labstag\Tests\AbstractWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

final class AdminIndexTest extends AbstractWebTestCase
{
    private function login(): void
    {
        $client = $this->client();
        $crawler = $client->request(Request::METHOD_GET, '/connexion');
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
            $crawler = $client->getCrawler();
        }
        $forms = $crawler->filter('form');
        if ($forms->count() === 0) {
            return;
        }
        $form = $forms->form([
            'login[username]' => 'superadmin',
            'login[password]' => 'password',
        ]);
        $client->submit($form);
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexBanIp(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/ban-ip');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexBlock(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/block');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexChapter(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/chapter');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexCompany(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/company');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexConfiguration(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/configuration');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexEdito(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/edito');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexEpisode(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/episode');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexFranchise(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/franchise');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexGameCategory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/game-category');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexGame(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/game');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexGeoCode(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/geo-code');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexGroup(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/group');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexHttpErrorLogs(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/http-error-logs');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexMedia(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/media');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexMemo(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/memo');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexMeta(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/meta');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexMovieCategory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/movie-category');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexMovie(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/movie');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexNotification(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/notification');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPageCategory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/page-category');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPage(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/page');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPageTag(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/page-tag');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexParagraph(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/paragraph');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPerson(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/person');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPlatform(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/platform');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPostCategory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/post-category');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPost(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/post');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexPostTag(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/post-tag');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexProfil(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/profil');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexRedirection(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/redirection');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexSaga(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/saga');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexSeason(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/season');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexSerieCategory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/serie-category');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexSerie(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/serie');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexStar(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/star');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexStoryCategory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/story-category');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexStory(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/story');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexStoryTag(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/story-tag');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexSubmission(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/submission');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexTemplate(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/template');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexUser(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/user');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }

    #[Test]
    #[Group('functional')]
    #[Group('controller')]
    #[Group('admin')]
    public function indexMessenger(): void
    {
        $this->login();
        $this->client()->request(Request::METHOD_GET, '/admin/messenger');
        $this->assertTrue(
            $this->client()->getResponse()->isSuccessful()
            || $this->client()->getResponse()->isRedirection()
        );
    }
}