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
        $page = new Page();
        $page->setType('home');

        $home = $page;

        $page = new Page();
        $page->setTitle('Histoires');
        $page->setType('history');

        $histories = $page;

        $page = new Page();
        $page->setTitle('Posts');
        $page->setType('post');

        $posts = $page;

        $page = new Page();
        $page->setTitle('Contact');
        $page->setType('page');

        $contact = $page;

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

        ];
    }

    private function setPage(ObjectManager $objectManager, Generator $generator, Page $page, array $data): void
    {
        $page->setEnable(true);
        $user = $this->getReference('user_superadmin', User::class);
        $page->setRefuser($user);
        $page->setTitle($page->getType());

        $date = $generator->unique()->dateTimeBetween('- 8 month', 'now');
        if (isset($data['parent'])) {
            $parent = $this->getReference('page_'.$data['parent'], Page::class);
            $page->setPage($parent);
            $date = $generator->unique()->dateTimeBetween($page->getCreatedAt(), '+1 week');
        }

        $page->setCreatedAt($date);

        $this->setReference('page_'.$page->getType(), $page);
        $objectManager->persist($page);
    }
}
