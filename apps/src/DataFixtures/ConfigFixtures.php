<?php

namespace Labstag\DataFixtures;

use Override;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Configuration;

class ConfigFixtures extends Fixture
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $configuration = new Configuration();
        $data          = $this->setData();
        foreach ($data as $key => $value) {
            $configuration = new Configuration();
            $configuration->setName($key);
            $configuration->setValue($value);
        }

        $objectManager->persist($configuration);
        $objectManager->flush();
    }

    private function setData()
    {
        return [
            'site_name' => [
                'type'  => 'string',
                'value' => 'labstag',
            ],
        ];
    }
}
