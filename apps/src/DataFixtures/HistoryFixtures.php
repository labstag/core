<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Override;

class HistoryFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_HISTORY = 10;

    #[Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_HISTORY, 'addHistory', $objectManager);
        $objectManager->flush();
    }

    protected function addHistory(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $meta = new Meta();
        $history = new History();
        $history->setMeta($meta);
        $history->setEnable((bool) rand(0, 1));
        $users = $this->getIdentitiesByClass(User::class);
        $history->setRefuser($this->getReference(array_rand($users)));
        $history->setTitle($generator->unique()->colorName());
        $this->addReference('history_'.md5(uniqid()), $history);
        $objectManager->persist($history);
    }
}
