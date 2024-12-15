<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Configuration;
use Override;

class ConfigFixtures extends Fixture
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $configuration = new Configuration();
        $configuration->setTitleFormat('%content_title% | %site_name%');
        $configuration->setSiteName('Labstag');
        $configuration->setUserShow(false);
        $configuration->setUserLink(false);

        $objectManager->persist($configuration);

        $objectManager->flush();
    }
}
