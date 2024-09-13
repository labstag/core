<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $objectManager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $objectManager->flush();
    }
}
