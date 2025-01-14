<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Configuration;
use Labstag\Lib\FixtureLib;
use Override;

class ConfigFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [TemplateFixtures::class];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $configuration = new Configuration();
        $configuration->setTitleFormat('%content_title% | %site_name%');
        $configuration->setName('Labstag');
        $configuration->setCopyright('Copyright since 1999');
        $configuration->setEmail('contact@labstag.traefik.me');
        $configuration->setUrl('https://labstag.traefik.me');
        $configuration->setNoreply('no-reply@labstag.traefik.me');
        $configuration->setUserShow(false);
        $configuration->setUserLink(false);
        $this->setImage($configuration, 'logoFile');
        $this->setImage($configuration, 'placeholderFile');

        $objectManager->persist($configuration);

        $objectManager->flush();
    }
}
