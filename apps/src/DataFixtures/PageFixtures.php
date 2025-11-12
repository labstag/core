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
        $sagas               = $this->setSaga($movies);
        $cvpage              = $this->setCv($page);
        $series              = $this->setSeries($page);
        $stories             = $this->setStories($page);
        $post                = $this->setPost($page);
        $star                = $this->setStar($page);
        $info                = $this->setInformations($page);
        $changepassword      = $this->setChangePassword($page);
        $login               = $this->setLogin($page);
        $lostpassword        = $this->setLostPassword($page);
        $contact             = $this->setContact($info);
        $sitemap             = $this->setSitemap($info);
        $mentions            = $this->setMentions($page);
        $donneespersonnelles = $this->setDonneesPersonelles($page);

        return [
            $page,
            $changepassword,
            $lostpassword,
            $login,
            $series,
            $movies,
            $stories,
            $post,
            $sagas,
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
        $this->addParagraphText($changepassword);
        $paragraph = $this->paragraphService->addParagraph($changepassword, 'form');
        if ($paragraph instanceof FormParagraph) {
            $paragraph->setSave(false);
            $paragraph->setContent('Formulaire envoyé');
            $paragraph->setForm('change-password');
        }


        return $changepassword;
    }

    private function setContact(Page $page): Page
    {
        $contact = new Page();
        $contact->setPage($page);
        $contact->setTitle('Contact');
        $contact->setType(PageEnum::PAGE->value);
        $this->addParagraphText($contact);
        $paragraph = $this->paragraphService->addParagraph($contact, 'form');
        if ($paragraph instanceof FormParagraph) {
            $paragraph->setSave(true);
            $paragraph->setContent('Formulaire envoyé');
            $paragraph->setForm('contact');
        }


        return $contact;
    }

    private function setCv(Page $page): Page
    {
        $cvpage = new Page();
        $cvpage->setPage($page);
        $cvpage->setTitle('Mon parcours pro');
        $cvpage->setType(PageEnum::CV->value);
        $this->paragraphService->addParagraph($cvpage, 'presentation-cv');
        $this->paragraphService->addParagraph($cvpage, 'competences');
        $this->paragraphService->addParagraph($cvpage, 'experiences');
        $this->paragraphService->addParagraph($cvpage, 'formations');

        return $cvpage;
    }

    private function setDonneesPersonelles(Page $page): Page
    {
        $donneespersonnelles = new Page();
        $donneespersonnelles->setPage($page);
        $donneespersonnelles->setTitle('Données personnelles');
        $donneespersonnelles->setType(PageEnum::PAGE->value);

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
        $this->addParagraphText($info);
        $this->paragraphService->addParagraph($info, 'sibling');

        return $info;
    }

    private function setLogin(Page $page): Page
    {
        $login = new Page();
        $login->setHide(true);
        $login->setPage($page);
        $login->setTitle('Connexion');
        $login->setType(PageEnum::LOGIN->value);
        $this->addParagraphText($login);
        $paragraph = $this->paragraphService->addParagraph($login, 'form');
        if ($paragraph instanceof FormParagraph) {
            $paragraph->setSave(true);
            $paragraph->setContent('Formulaire envoyé');
            $paragraph->setForm('login');
        }


        return $login;
    }

    private function setLostPassword(Page $page): Page
    {
        $lostpassword = new Page();
        $lostpassword->setHide(true);
        $lostpassword->setPage($page);
        $lostpassword->setTitle('Mot de passe oublié');
        $lostpassword->setType(PageEnum::LOSTPASSWORD->value);
        $this->addParagraphText($lostpassword);
        $paragraph = $this->paragraphService->addParagraph($lostpassword, 'form');
        if ($paragraph instanceof FormParagraph) {
            $paragraph->setSave(true);
            $paragraph->setContent('Formulaire envoyé');
            $paragraph->setForm('lost-password');
        }


        return $lostpassword;
    }

    private function setMentions(Page $page): Page
    {
        $mentions = new Page();
        $mentions->setPage($page);
        $mentions->setTitle('Mentions légales');
        $mentions->setType(PageEnum::PAGE->value);

        $this->addParagraphText($mentions);

        return $mentions;
    }

    public function setSaga(Page $page): Page
    {
        $sagas = new Page();
        $sagas->setPage($page);
        $sagas->setTitle('Mes sagas favorites');
        $sagas->setType(PageEnum::PAGE->value);

        $this->addParagraphText($sagas);
        $this->paragraphService->addParagraph($sagas, 'saga');

        return $sagas;
    }

    private function setMovies(Page $page): Page
    {
        $movies = new Page();
        $movies->setPage($page);
        $movies->setTitle('Mes derniers films vus');
        $movies->setType(PageEnum::MOVIES->value);
        $this->addParagraphText($movies);
        $paragraph = $this->paragraphService->addParagraph($movies, 'movie');
        if ($paragraph instanceof MovieParagraph) {
            $paragraph->setNbr(18);
        }


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

    private function setParagraphsHome(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'hero');
        $paragraph = $this->paragraphService->addParagraph($page, 'edito');
        if ($paragraph instanceof VideoParagraph) {
            $paragraph->setTitle('edito');
        }

        $this->addParagraphText($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'last-news');
        if ($paragraph instanceof LastNewsParagraph) {
            $paragraph->setTitle('Dernières news');
            $paragraph->setNbr(4);
        }

        $paragraph = $this->paragraphService->addParagraph($page, 'last-story');
        if ($paragraph instanceof LastStoryParagraph) {
            $paragraph->setTitle('Dernière histoires');
            $paragraph->setNbr(4);
        }

        $this->setParagraphsHomeVideo($page);
        $paragraph = $this->paragraphService->addParagraph($page, 'movie-slider');
        if ($paragraph instanceof MovieSliderParagraph) {
            $paragraph->setTitle('Mes derniers films vus');
            $paragraph->setNbr(12);
        }
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

    private function setPost(Page $page): Page
    {
        $post = new Page();
        $post->setPage($page);
        $post->setTitle('Posts');
        $post->setType(PageEnum::POSTS->value);
        $this->addParagraphText($post);
        $paragraph = $this->paragraphService->addParagraph($post, 'news-list');
        if ($paragraph instanceof NewsListParagraph) {
            $paragraph->setNbr(18);
        }


        return $post;
    }

    private function setSeries(Page $page): Page
    {
        $series = new Page();
        $series->setPage($page);
        $series->setTitle('Mes séries favorites');
        $series->setType(PageEnum::SERIES->value);
        $this->addParagraphText($series);
        $paragraph = $this->paragraphService->addParagraph($series, 'serie');
        if ($paragraph instanceof SerieParagraph) {
            $paragraph->setNbr(18);
        }


        return $series;
    }

    private function setSitemap(Page $page): Page
    {
        $sitemap = new Page();
        $sitemap->setPage($page);
        $sitemap->setTitle('Plan du site');
        $sitemap->setType(PageEnum::PAGE->value);
        $this->addParagraphText($sitemap);
        $this->paragraphService->addParagraph($sitemap, 'sitemap');

        return $sitemap;
    }

    private function setStar(Page $page): Page
    {
        $star = new Page();
        $star->setPage($page);
        $star->setTitle('Mes étoiles github');
        $star->setType(PageEnum::PAGE->value);
        $this->addParagraphText($star);
        $paragraph = $this->paragraphService->addParagraph($star, 'star');
        if ($paragraph instanceof StarParagraph) {
            $paragraph->setNbr(18);
        }


        return $star;
    }

    private function setStories(Page $page): Page
    {
        $stories = new Page();
        $stories->setPage($page);
        $stories->setTitle('Histoires');
        $stories->setType(PageEnum::STORIES->value);
        $this->addParagraphText($stories);
        $paragraph = $this->paragraphService->addParagraph($stories, 'story-list');
        if ($paragraph instanceof StoryListParagraph) {
            $paragraph->setNbr(18);
        }

        return $stories;
    }
}
