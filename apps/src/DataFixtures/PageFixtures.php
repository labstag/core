<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\FormParagraph;
use Labstag\Entity\LastNewsParagraph;
use Labstag\Entity\LastStoryParagraph;
use Labstag\Entity\MovieParagraph;
use Labstag\Entity\MovieSliderParagraph;
use Labstag\Entity\NewsListParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\PageCategory;
use Labstag\Entity\PageTag;
use Labstag\Entity\SerieParagraph;
use Labstag\Entity\StarParagraph;
use Labstag\Entity\StoryListParagraph;
use Labstag\Entity\User;
use Labstag\Entity\VideoParagraph;
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
        $this->tags       = $this->getIdentitiesByClass(PageTag::class);
        $this->categories = $this->getIdentitiesByClass(PageCategory::class);
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
        $page                = $this->setHome();
        $movies              = $this->setMovies($page);
        $cvpage              = $this->setCv($page);
        $series              = $this->setSeries($page);
        $stories             = $this->setStories($page);
        $post                = $this->setPost($page);
        $star                = $this->setStar($page);
        $info                = $this->setInformations($page);
        $changepassword      = $this->setChangePassword($page);
        $lostpassword        = $this->setLostPassword($page);
        $login               = $this->setLogin($page);
        $contact             = $this->setContact($info);
        $sitemap             = $this->setSitemap($info);
        $mentions            = $this->setMentions($page);
        $donneespersonnelles = $this->setDonneesPersonelles($page);

        return [
            $page,
            $login,
            $changepassword,
            $lostpassword,
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

    private function setChangePassword(Page $page): Page
    {
        $changepassword = new Page();
        $changepassword->setHide(true);
        $changepassword->setPage($page);
        $changepassword->setTitle('Changer de mot de passe');
        $changepassword->setType(PageEnum::CHANGEPASSWORD->value);
        $this->setParagraphsChangePassword($changepassword);

        return $changepassword;
    }

    private function setContact(Page $page): Page
    {
        $contact = new Page();
        $contact->setPage($page);
        $contact->setTitle('Contact');
        $contact->setType(PageEnum::PAGE->value);
        $this->setParagraphsContact($contact);

        return $contact;
    }

    private function setCv(Page $page): Page
    {
        $cvpage = new Page();
        $cvpage->setPage($page);
        $cvpage->setTitle('Mon parcours pro');
        $cvpage->setType(PageEnum::CV->value);
        $this->setParagraphsCV($cvpage);

        return $cvpage;
    }

    private function setDonneesPersonelles(Page $page): Page
    {
        $donneespersonnelles = new Page();
        $donneespersonnelles->setPage($page);
        $donneespersonnelles->setTitle('Données personnelles');
        $donneespersonnelles->setType(PageEnum::PAGE->value);

        $this->paragraphService->addParagraph($donneespersonnelles, 'head');
        $this->addParagraphText($donneespersonnelles);

        return $donneespersonnelles;
    }

    private function setHome(): Page
    {
        $page = new Page();
        $page->setTitle('Accueil');
        $page->setType(PageEnum::HOME->value);
        $this->setParagraphsHome($page);

        return $page;
    }

    private function setInformations(Page $page): Page
    {
        $info = new Page();
        $info->setPage($page);
        $info->setTitle('Informations');
        $info->setType(PageEnum::PAGE->value);
        $this->setParagraphsInfo($info);

        return $info;
    }

    private function setLogin(Page $page): Page
    {
        $login = new Page();
        $login->setHide(true);
        $login->setPage($page);
        $login->setTitle('Login');
        $login->setType(PageEnum::LOGIN->value);
        $this->setParagraphsLogin($login);

        return $login;
    }

    private function setLostPassword(Page $page): Page
    {
        $lostpassword = new Page();
        $lostpassword->setHide(true);
        $lostpassword->setPage($page);
        $lostpassword->setTitle('Mot de passe oublié');
        $lostpassword->setType(PageEnum::LOSTPASSWORD->value);
        $this->setParagraphsLostPassword($lostpassword);

        return $lostpassword;
    }

    private function setMentions(Page $page): Page
    {
        $mentions = new Page();
        $mentions->setPage($page);
        $mentions->setTitle('Mentions légales');
        $mentions->setType(PageEnum::PAGE->value);

        $this->paragraphService->addParagraph($mentions, 'head');
        $this->addParagraphText($mentions);

        return $mentions;
    }

    private function setMovies(Page $page): Page
    {
        $movies = new Page();
        $movies->setPage($page);
        $movies->setTitle('Mes derniers films vus');
        $movies->setType(PageEnum::MOVIES->value);
        $this->setParagraphsMovie($movies);

        return $movies;
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
        $this->addTagToEntity($page, PageTag::class);
        $this->addCategoryToEntity($page, PageCategory::class);

        $this->addReference('page_' . md5(uniqid()), $page);
        $objectManager->persist($page);
    }

    private function setParagraphsChangePassword(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph) || !$paragraph instanceof FormParagraph) {
            return;
        }

        $paragraph->setSave(true);
        $paragraph->setContent('Formulaire envoyé');
        $paragraph->setForm('change-password');
    }

    private function setParagraphsContact(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph) || !$paragraph instanceof FormParagraph) {
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
        if (is_null($paragraph) || !$paragraph instanceof VideoParagraph) {
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
        if (is_null($paragraph) || !$paragraph instanceof LastNewsParagraph) {
            return;
        }

        $paragraph->setTitle('Dernières news');
        $paragraph->setNbr(4);
    }

    private function setParagraphsHomeLastStory(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'last-story');
        if (is_null($paragraph) || !$paragraph instanceof LastStoryParagraph) {
            return;
        }

        $paragraph->setTitle('Dernière histoires');
        $paragraph->setNbr(4);
    }

    private function setParagraphsHomeMovieSlider(Page $page): void
    {
        $paragraph = $this->paragraphService->addParagraph($page, 'movie-slider');
        if (is_null($paragraph) || !$paragraph instanceof MovieSliderParagraph) {
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
        if (is_null($paragraph) || !$paragraph instanceof VideoParagraph) {
            return;
        }

        $paragraph->setTitle('Vidéo');
        $this->setImage($paragraph, 'imgFile');
        $paragraph->setUrl($generator->youtubeUri());
    }

    private function setParagraphsInfo(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $this->paragraphService->addParagraph($page, 'sibling');
    }

    private function setParagraphsLogin(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
    }

    private function setParagraphsLostPassword(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph) || !$paragraph instanceof FormParagraph) {
            return;
        }

        $paragraph->setSave(true);
        $paragraph->setContent('Formulaire envoyé');
        $paragraph->setForm('lost-password');
    }

    private function setParagraphsMovie(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'saga');
        $paragraph = $this->paragraphService->addParagraph($page, 'movie');
        if (is_null($paragraph) || !$paragraph instanceof MovieParagraph) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsPost(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'news-list');
        if (is_null($paragraph) || !$paragraph instanceof NewsListParagraph) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsSerie(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'serie');
        if (is_null($paragraph) || !$paragraph instanceof SerieParagraph) {
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
        if (is_null($paragraph) || !$paragraph instanceof StarParagraph) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setParagraphsStory(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'head');
        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'story-list');
        if (is_null($paragraph) || !$paragraph instanceof StoryListParagraph) {
            return;
        }

        $paragraph->setNbr(18);
    }

    private function setPost(Page $page): Page
    {
        $post = new Page();
        $post->setPage($page);
        $post->setTitle('Posts');
        $post->setType(PageEnum::POSTS->value);
        $this->setParagraphsPost($post);

        return $post;
    }

    private function setSeries(Page $page): Page
    {
        $series = new Page();
        $series->setPage($page);
        $series->setTitle('Mes séries favorites');
        $series->setType(PageEnum::SERIES->value);
        $this->setParagraphsSerie($series);

        return $series;
    }

    private function setSitemap(Page $page): Page
    {
        $sitemap = new Page();
        $sitemap->setPage($page);
        $sitemap->setTitle('Plan du site');
        $sitemap->setType(PageEnum::PAGE->value);
        $this->setParagraphsSitemap($sitemap);

        return $sitemap;
    }

    private function setStar(Page $page): Page
    {
        $star = new Page();
        $star->setPage($page);
        $star->setTitle('Mes étoiles github');
        $star->setType(PageEnum::PAGE->value);
        $this->setParagraphsStar($star);

        return $star;
    }

    private function setStories(Page $page): Page
    {
        $stories = new Page();
        $stories->setPage($page);
        $stories->setTitle('Histoires');
        $stories->setType(PageEnum::STORIES->value);
        $this->setParagraphsStory($stories);

        return $stories;
    }
}
