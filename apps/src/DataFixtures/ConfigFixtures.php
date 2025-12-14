<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Configuration;
use Labstag\Entity\User;
use Override;

class ConfigFixtures extends FixtureAbstract implements DependentFixtureInterface
{
    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TemplateFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $configuration = new Configuration();
        $configuration->setDefaultUser($this->getReference('user_superadmin', User::class));
        $configuration->setTitleFormat('%content_title% | %site_name%');
        $configuration->setName('Labstag');
        $configuration->setCopyright('Copyright since 1999');
        $configuration->setEmail('contact@labstag.traefik.me');
        $configuration->setUrl('https://labstag.traefik.me');
        $configuration->setNoreply('no-reply@labstag.traefik.me');
        $configuration->setUserShow(false);
        $configuration->setUserLink(false);
        $configuration->setLanguageTmdb('fr-FR');
        $configuration->setRegionTmdb('FR');
        $configuration->setDisableEmptyAgent(false);
        $this->setImage($configuration, 'logoFile');
        $this->setImage($configuration, 'placeholderFile');
        $this->setImage($configuration, 'chapterPlaceholder');
        $this->setImage($configuration, 'editoPlaceholder');
        $this->setImage($configuration, 'episodePlaceholder');
        $this->setImage($configuration, 'memoPlaceholder');
        $this->setImage($configuration, 'moviePlaceholder');
        $this->setImage($configuration, 'gamePlaceholder');
        $this->setImage($configuration, 'postPlaceholder');
        $this->setImage($configuration, 'sagaPlaceholder');
        $this->setImage($configuration, 'starPlaceholder');
        $this->setImage($configuration, 'storyPlaceholder');
        $this->setImage($configuration, 'userPlaceholder');

        $objectManager->persist($configuration);

        $objectManager->flush();
    }
}
