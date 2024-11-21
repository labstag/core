<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Override;

class PageFixtures extends FixtureLib implements DependentFixtureInterface
{
    #[Override]
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $generator        = $this->setFaker();
        $data             = $this->data();
        $this->tags       = $this->getIdentitiesByClass(Tag::class, 'page');
        $this->categories = $this->getIdentitiesByClass(Category::class, 'page');
        foreach ($data as $row) {
            $entity = $row['entity'];
            unset($row['entity']);
            $this->setPage($objectManager, $generator, $entity, $row);
        }

        $objectManager->flush();
    }

    private function data(): array
    {
        $home = new Page();
        $home->setTitle('Accueil');
        $home->setType('home');
        $this->setParagraphsHome($home);

        $histories = new Page();
        $histories->setTitle('Histoires');
        $histories->setType('history');
        $this->setParagraphsHistory($histories);

        $posts = new Page();
        $posts->setTitle('Posts');
        $posts->setType('post');
        $this->setParagraphsPost($posts);

        $contact = new Page();
        $contact->setTitle('Contact');
        $contact->setType('page');

        $sitemap = new Page();
        $sitemap->setTitle('Plan du site');
        $sitemap->setType('page');
        $this->setParagraphsSitemap($sitemap);

        return [
            ['entity' => $home],
            [
                'entity' => $histories,
                'parent' => 'home',
            ],
            [
                'entity' => $posts,
                'parent' => 'home',
            ],
            [
                'entity' => $contact,
                'parent' => 'home',
            ],
            [
                'entity' => $sitemap,
                'parent' => 'home',
            ],

        ];
    }

    private function setPage(ObjectManager $objectManager, Generator $generator, Page $page, array $data): void
    {
        $page->setEnable(true);
        $page->setResume($generator->unique()->text(200));

        $user = $this->getReference('user_superadmin', User::class);
        $page->setRefuser($user);

        $date = $generator->unique()->dateTimeBetween('- 8 month', 'now');
        if (isset($data['parent'])) {
            $parent = $this->getReference('page_'.$data['parent'], Page::class);
            $page->setPage($parent);
            $date = $generator->unique()->dateTimeBetween($page->getCreatedAt(), '+1 week');
        }

        $page->setCreatedAt($date);
        $this->setImage($page, 'imgFile');

        $this->setReference('page_'.$page->getType(), $page);
        $objectManager->persist($page);
    }

    private function setParagraphsHistory(Page $page)
    {
        $generator = $this->setFaker();
        $paragraph = new Paragraph();
        $paragraph->setType('text');
        $paragraph->setContent($generator->text(500));
        
        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Dernière histoires');
        $paragraph->setType('history-list');
        $paragraph->setNbr(20);

        $page->addParagraph($paragraph);
    }

    private function setParagraphsHome(Page $page)
    {
        $generator = $this->setFaker();

        $paragraph = new Paragraph();
        $paragraph->setTitle('edito');
        $paragraph->setType('edito');

        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Texte');
        $paragraph->setType('text');
        $paragraph->setContent($generator->text(500));

        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Dernière news');
        $paragraph->setType('last-news');
        $paragraph->setNbr(4);

        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Dernière histoires');
        $paragraph->setType('last-history');
        $paragraph->setNbr(4);

        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Dernière histoires');
        $paragraph->setType('video');
        $this->setImage($paragraph, 'imgFile');
        $paragraph->setUrl($generator->youtubeUri());
        $page->addParagraph($paragraph);
    }

    private function setParagraphsPost(Page $page)
    {
        $generator = $this->setFaker();
        $paragraph = new Paragraph();
        $paragraph->setType('text');
        $paragraph->setContent($generator->text(500));

        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Derniers posts');
        $paragraph->setType('news-list');
        $paragraph->setNbr(20);

        $page->addParagraph($paragraph);
    }

    private function setParagraphsSitemap(Page $page)
    {
        $generator = $this->setFaker();
        $paragraph = new Paragraph();
        $paragraph->setType('text');
        $paragraph->setContent($generator->text(500));

        $page->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setTitle('Sitemap');
        $paragraph->setType('sitemap');
        $paragraph->setNbr(20);

        $page->addParagraph($paragraph);
    }
}
