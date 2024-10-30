<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Memo;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Override;

class MemoFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_MEMO = 10;

    #[Override]
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_MEMO, 'addMemo', $objectManager);
        $objectManager->flush();
    }

    protected function addMemo(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $memo = new Memo();
        $memo->setEnable((bool) random_int(0, 1));

        $users = $this->getIdentitiesByClass(User::class);
        $memo->setRefuser($this->getReference(array_rand($users)));
        $memo->setTitle($generator->unique()->colorName());
        $this->addReference('memo_'.md5(uniqid()), $memo);
        $objectManager->persist($memo);
    }
}
