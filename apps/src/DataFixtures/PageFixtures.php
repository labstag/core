<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Page;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Enum\PageEnum;
use Override;

class PageFixtures extends FixtureAbstract implements DependentFixtureInterface
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

    public function getParent(mixed $idParent): ?object
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
        $home->setType(PageEnum::HOME->value);
        $this->setParagraphsHome($home);

        $movies = new Page();
        $movies->setPage($home);
        $movies->setTitle('Mes derniers films vus');
        $movies->setType(PageEnum::MOVIES->value);
        $this->setParagraphsMovie($movies);

        $cvpage = new Page();
        $cvpage->setPage($home);
        $cvpage->setTitle('Mon parcours pro');
        $cvpage->setType(PageEnum::CV->value);
        $this->setParagraphsCV($cvpage);

        $series = new Page();
        $series->setPage($home);
        $series->setTitle('Mes séries favorites');
        $series->setType(PageEnum::SERIES->value);
        $this->setParagraphsSerie($series);

        $stories = new Page();
        $stories->setPage($home);
        $stories->setTitle('Histoires');
        $stories->setType(PageEnum::STORIES->value);
        $this->setParagraphsStory($stories);

        $post = new Page();
        $post->setPage($home);
        $post->setTitle('Posts');
        $post->setType(PageEnum::POSTS->value);
        $this->setParagraphsPost($post);

        $star = new Page();
        $star->setPage($home);
        $star->setTitle('Mes étoiles github');
        $star->setType(PageEnum::PAGE->value);
        $this->setParagraphsStar($star);

        $info = new Page();
        $info->setPage($home);
        $info->setTitle('Informations');
        $info->setType(PageEnum::PAGE->value);
        $this->setParagraphsInfo($info);

        $contact = new Page();
        $contact->setPage($info);
        $contact->setTitle('Contact');
        $contact->setType(PageEnum::PAGE->value);
        $this->setParagraphsContact($contact);

        $sitemap = new Page();
        $sitemap->setPage($info);
        $sitemap->setTitle('Plan du site');
        $sitemap->setType(PageEnum::PAGE->value);
        $this->setParagraphsSitemap($sitemap);

        $mentions = new Page();
        $mentions->setPage($home);
        $mentions->setTitle('Mentions légales');
        $mentions->setType(PageEnum::PAGE->value);

        $this->paragraphService->addParagraph($mentions, 'head');
        $this->addParagraphText($mentions);

        $donneespersonnelles = new Page();
        $donneespersonnelles->setPage($home);
        $donneespersonnelles->setTitle('Données personnelles');
        $donneespersonnelles->setType(PageEnum::PAGE->value);

        $this->paragraphService->addParagraph($donneespersonnelles, 'head');
        $this->addParagraphText($donneespersonnelles);

        return [
            $home,
            $series,
            $movies,
            $stories,
            $post,
            $star,
            $info,
            $contact,
            $sitemap,
            $mentions,
            $cvpage,
            $donneespersonnelles,
        ];
    }

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
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setSave(true);
        $paragraph->setContent('Formulaire envoyé');
        $paragraph->setForm('contact');
    }

    private function setParagraphsCv(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->paragraphService->addParagraph($page, 'presentation-cv');
        $this->paragraphService->addParagraph($page, 'competences');
        $this->paragraphService->addParagraph($page, 'experiences');
        $this->paragraphService->addParagraph($page, 'formations');
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

    private function setParagraphsHomeMovieSlider(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'movie-slider');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setTitle('Mes derniers films vus');
        $paragraph->setNbr(12);
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

    private function setParagraphsInfo(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $this->paragraphService->addParagraph($page, 'sibling');
    }

    private function setParagraphsMovie(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'saga');
        $paragraph = $this->paragraphService->addParagraph($page, 'movie');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsPost(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'news-list');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsSerie(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'serie');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsSitemap(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $this->paragraphService->addParagraph($page, 'sitemap');
    }

    private function setParagraphsStar(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'star');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsStory(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'story-list');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setNbr(18);
    }
}
