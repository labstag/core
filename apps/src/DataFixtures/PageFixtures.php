<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Page;
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

        $stories = new Page();
        $stories->setTitle('Histoires');
        $stories->setType('story');
        $this->setParagraphsStory($stories);

        $post = new Page();
        $post->setTitle('Posts');
        $post->setType('post');
        $this->setParagraphsPost($post);

        $star = new Page();
        $star->setTitle('Mes étoiles github');
        $star->setType('page');
        $this->setParagraphsStar($star);

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
                'entity' => $stories,
                'parent' => 'home',
            ],
            [
                'entity' => $post,
                'parent' => 'home',
            ],
            [
                'entity' => $star,
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

    private function setParagraphsHome(Page $page)
    {
        $generator = $this->setFaker();
        $paragraph = $this->paragraphService->addParagraph($page, 'edito');
        $paragraph->setTitle('edito');

        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'last-news');
        $paragraph->setTitle('Dernières news');
        $paragraph->setType('last-news');
        $paragraph->setNbr(4);

        $paragraph = $this->paragraphService->addParagraph($page, 'last-story');
        $paragraph->setTitle('Dernière histoires');
        $paragraph->setNbr(4);

        $paragraph = $this->paragraphService->addParagraph($page, 'video');
        $paragraph->setTitle('Vidéo');
        $paragraph->setType('video');
        $this->setImage($paragraph, 'imgFile');
        $paragraph->setUrl($generator->youtubeUri());
    }

    private function setParagraphsPost(Page $page)
    {
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'news-list');
        $paragraph->setTitle('Derniers posts');
        $paragraph->setNbr(20);
    }

    private function setParagraphsSitemap(Page $page)
    {
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'sitemap');
        $paragraph->setTitle('Sitemap');
        $paragraph->setNbr(20);
    }

    private function setParagraphsStar(Page $page)
    {
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'star');
        $paragraph->setTitle('Mes étoiles github');
        $paragraph->setNbr(20);
    }

    private function setParagraphsStory(Page $page)
    {
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'story-list');
        $paragraph->setTitle('Dernière histoires');
        $paragraph->setNbr(20);
    }
}
