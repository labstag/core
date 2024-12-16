<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Configuration;
use Labstag\Lib\FixtureLib;
use Override;

class ConfigFixtures extends FixtureLib
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $configuration = new Configuration();
        $configuration->setTitleFormat('%content_title% | %site_name%');
        $configuration->setSiteName('Labstag');
        $configuration->setUserShow(false);
        $configuration->setUserLink(false);
        $this->setImage($configuration, 'logoFile');
        $this->setImage($configuration, 'placeholderFile');

        $objectManager->persist($configuration);

        $objectManager->flush();
    }
}
