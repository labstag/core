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
        $data          = $this->setData();
        foreach ($data as $key => $value) {
            $configuration = new Configuration();
            $configuration->setName($key);
            $configuration->setValue($value);
            $objectManager->persist($configuration);
        }

        $objectManager->flush();
    }

    private function setData()
    {
        return [
            'title_format' => [
                'type'  => 'string',
                'value' => '%content_title% | %site_name%',
            ],
            'site_name'    => [
                'type'  => 'string',
                'value' => 'labstag',
            ],
            'user_show' => [
                'type' => 'boolean',
                'value' => false,
            ],
            'user_link' => [
                'type' => 'boolean',
                'value' => false,
            ]
        ];
    }
}
