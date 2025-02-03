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

    /**
     * @var User[]
     */
    protected array $users = [];

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->users = $this->getIdentitiesByClass(User::class);
        $this->loadForeach(self::NUMBER_MEMO, 'addMemo', $objectManager);
        $objectManager->flush();
    }

    protected function addMemo(Generator $generator, ObjectManager $objectManager, int $index): void
    {
        $memo = new Memo();
        $memo->setCreatedAt($generator->unique()->dateTimeBetween('- 8 month', 'now'));
        $memo->setEnable($this->enable === $index);
        $memo->setRefuser($this->getReference(array_rand($this->users), User::class));
        $memo->setTitle($generator->unique()->colorName());
        $this->addParagraphText($memo);
        $this->setImage($memo, 'imgFile');
        $this->addReference('memo_' . md5(uniqid()), $memo);
        $objectManager->persist($memo);
    }
}
