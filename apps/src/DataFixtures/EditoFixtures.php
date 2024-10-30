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
        $this->loadForeach(self::NUMBER_EDITO, 'addEdito', $objectManager);
        $objectManager->flush();
    }

    protected function addEdito(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $edito = new Edito();
        $edito->setEnable((bool) random_int(0, 1));

        $users = $this->getIdentitiesByClass(User::class);
        $edito->setRefuser($this->getReference(array_rand($users)));
        $edito->setTitle($generator->unique()->colorName());
        $this->addReference('edito_'.md5(uniqid()), $edito);
        $objectManager->persist($edito);
    }
}
