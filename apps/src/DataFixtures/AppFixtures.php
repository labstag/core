<?php

namespace Labstag\DataFixtures;

use Override;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $objectManager->flush();
    }
}
