<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Edito;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Override;

class EditoFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_EDITO = 10;

    protected array $users = [];

    #[Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->users = $this->getIdentitiesByClass(User::class);
        $this->loadForeach(self::NUMBER_EDITO, 'addEdito', $objectManager);
        $objectManager->flush();
    }

    protected function addEdito(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $edito = new Edito();
        $edito->setCreatedAt($generator->unique()->dateTimeBetween('- 8 month', 'now'));
        $edito->setEnable((bool) random_int(0, 1));
        $edito->setRefuser($this->getReference(array_rand($this->users), User::class));
        $edito->setTitle($generator->unique()->colorName());
        $this->addReference('edito_'.md5(uniqid()), $edito);
        $objectManager->persist($edito);
    }
}
