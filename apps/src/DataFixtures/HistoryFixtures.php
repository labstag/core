<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Override;

class HistoryFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_HISTORY = 50;

    protected array $users = [];

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
        $this->users      = $this->getIdentitiesByClass(User::class);
        $this->tags       = $this->getIdentitiesByClass(Tag::class, 'history');
        $this->categories = $this->getIdentitiesByClass(Category::class, 'history');
        $this->loadForeach(self::NUMBER_HISTORY, 'addHistory', $objectManager);
        $objectManager->flush();
    }

    protected function addHistory(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $meta    = new Meta();
        $history = new History();
        $history->setCreatedAt($generator->unique()->dateTimeBetween('- 8 month', 'now'));
        $history->setMeta($meta);
        $history->setEnable((bool) random_int(0, 1));
        $history->setRefuser($this->getReference(array_rand($this->users), User::class));
        $history->setTitle($generator->unique()->colorName());
        $this->setImage($history, 'imgFile');
        $this->addTagToEntity($history);
        $this->addCategoryToEntity($history);
        $this->addReference('history_'.md5(uniqid()), $history);
        $objectManager->persist($history);
    }
}
