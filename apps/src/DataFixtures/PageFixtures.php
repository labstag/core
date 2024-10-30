<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Page;
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
        $data = $this->data();
        foreach ($data as $row) {
            $entity = $row['entity'];
            unset($row['entity']);
            $this->setPage($objectManager, $entity, $row);
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

    private function setPage(ObjectManager $objectManager, Page $page, array $data): void
    {
        $page->setEnable(true);
        $user = $this->getReference('user_superadmin', User::class);
        $page->setRefuser($user);
        $page->setTitle($page->getType());
        if (isset($data['parent'])) {
            $parent = $this->getReference('page_'.$data['parent'], Page::class);
            $page->setPage($parent);
        }

        $this->setReference('page_'.$page->getType(), $page);
        $objectManager->persist($page);
    }
}
