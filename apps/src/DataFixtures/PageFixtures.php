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
    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    public function getParent($idParent): ?object
    {
        $parent = null;
        $pages  = $this->getIdentitiesByClass(Page::class);
        foreach ($pages as $id) {
            $page = $this->getReference($id, Page::class);
            if ($page->getType() != $idParent) {
                continue;
            }

            $parent = $page;

            break;
        }

        return $parent;
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $generator        = $this->setFaker();
        $data             = $this->data();
        $this->tags       = $this->getIdentitiesByClass(Tag::class, 'page');
        $this->categories = $this->getIdentitiesByClass(Category::class, 'page');
        foreach ($data as $entity) {
            $this->setPage($objectManager, $generator, $entity);
        }

        $objectManager->flush();
    }

    /**
     * @return mixed[]
     */
    private function data(): array
    {
        $home = new Page();
        $home->setTitle('Accueil');
        $home->setType('home');
        $this->setParagraphsHome($home);

        $movies = new Page();
        $movies->setPage($home);
        $movies->setTitle('Mes derniers films vus');
        $movies->setType('movie');
        $this->setParagraphsMovie($movies);

        $stories = new Page();
        $stories->setPage($home);
        $stories->setTitle('Histoires');
        $stories->setType('story');
        $this->setParagraphsStory($stories);

        $post = new Page();
        $post->setPage($home);
        $post->setTitle('Posts');
        $post->setType('post');
        $this->setParagraphsPost($post);

        $star = new Page();
        $star->setPage($home);
        $star->setTitle('Mes étoiles github');
        $star->setType('page');
        $this->setParagraphsStar($star);

        $info = new Page();
        $info->setPage($home);
        $info->setTitle('Informations');
        $info->setType('page');
        $this->setParagraphsInfo($info);

        $contact = new Page();
        $contact->setPage($info);
        $contact->setTitle('Contact');
        $contact->setType('page');
        $this->setParagraphsContact($contact);

        $sitemap = new Page();
        $sitemap->setPage($info);
        $sitemap->setTitle('Plan du site');
        $sitemap->setType('page');
        $this->setParagraphsSitemap($sitemap);

        return [
            $home,
            $movies,
            $stories,
            $post,
            $star,
            $info,
            $contact,
            $sitemap,
        ];
    }

    private function setParagraphsInfo(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $this->paragraphService->addParagraph($page, 'sibling');
    }

    /**
     * @param array{entity: Page, parent: string} $data
     */
    private function setPage(ObjectManager $objectManager, Generator $generator, Page $page): void
    {
        $page->setEnable(true);
        $page->setResume($generator->unique()->text(200));

        $user = $this->getReference('user_superadmin', User::class);
        $page->setRefuser($user);

        $date = $generator->unique()->dateTimeBetween('- 8 month', 'now');

        $page->setCreatedAt($date);
        $this->setImage($page, 'imgFile');
        $this->addTagToEntity($page);
        $this->addCategoryToEntity($page);

        $this->addReference('page_' . md5(uniqid()), $page);
        $objectManager->persist($page);
    }

    private function setParagraphsContact(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Formulaire de contact');
        $paragraph->setForm('contact');
    }

    private function setParagraphsHomeEdito(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'edito');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('edito');
    }

    private function setParagraphsHomeHero(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'hero');
    }

    private function setParagraphsHomeLastNews(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'last-news');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Dernières news');
        $paragraph->setType('last-news');
        $paragraph->setNbr(4);
    }

    private function setParagraphsHomeLastStory(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'last-story');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Dernière histoires');
        $paragraph->setNbr(4);
    }

    private function setParagraphsHomeVideo(Page $page): void
    {
        $generator = $this->setFaker();
        if (!method_exists($generator, 'youtubeUri')) {
            return;
        }

        $paragraph = $this->paragraphService->addParagraph($page, 'video');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Vidéo');
        $paragraph->setType('video');
        $this->setImage($paragraph, 'imgFile');
        $paragraph->setUrl($generator->youtubeUri());
    }

    private function setParagraphsHome(Page $page): void
    {
        $this->setParagraphsHomeHero($page);
        $this->setParagraphsHomeEdito($page);
        $this->addParagraphText($page);
        $this->setParagraphsHomeLastNews($page);
        $this->setParagraphsHomeLastStory($page);
        $this->setParagraphsHomeVideo($page);
        $this->setParagraphsHomeMovieSlider($page);
    }

    private function setParagraphsHomeMovieSlider(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'movie-slider');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Mes derniers films vus');
        $paragraph->setNbr(3);

    }

    private function setParagraphsPost(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'news-list');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Derniers posts');
        $paragraph->setNbr(18);
    }

    private function setParagraphsSitemap(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'sitemap');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Sitemap');
    }

    private function setParagraphsStar(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'star');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Mes étoiles github');
        $paragraph->setNbr(18);
    }

    private function setParagraphsMovie(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'movie');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Derniers films vus');
        $paragraph->setNbr(18);
    }

    private function setParagraphsStory(Page $page): void
    {
        $this->addParagraphHead($page);
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'story-list');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Dernière histoires');
        $paragraph->setNbr(18);
    }
}
