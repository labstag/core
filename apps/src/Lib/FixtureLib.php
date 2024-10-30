<?php

namespace Labstag\Lib;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class FixtureLib extends Fixture
{
    protected function getIdentitiesByClass(string $class): array
    {
        $data = $this->referenceRepository->getIdentitiesByClass();

        return $data[$class] ?? [];
    }

    protected function loadForeach(
        int $number,
        string $method,
        ObjectManager $objectManager
    ): void
    {
        $generator = $this->setFaker();
        for ($index = 0; $index < $number; ++$index) {
            call_user_func([$this, $method], $generator, $objectManager);
        }
    }

    protected function setFaker(): Generator
    {
        return Factory::create('fr_FR');
    }
}
