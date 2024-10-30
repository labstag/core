<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Tag;
use Labstag\Lib\FixtureLib;

class TagFixtures extends FixtureLib
{
    /**
     * @var int
     */
    protected const NUMBER_TAGS = 30;

    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_TAGS, 'addTag', $objectManager);
        $objectManager->flush();
    }

    protected function addTag(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $tab = [
            'chapter', 'edito', 'history', 'memo', 'page', 'post'
        ];
        $code = $tab[array_rand($tab)];
        $category = new Tag();
        $category->setTitle($generator->unique()->colorName());
        $category->setType($code);
        $this->addReference('tag'.$code.'_'.md5(uniqid()), $category);
        $objectManager->persist($category);
    }
}
