<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Tag;
use Labstag\Lib\FixtureLib;
use Override;

class TagFixtures extends FixtureLib
{
    /**
     * @var int
     */
    protected const NUMBER_TAGS = 30;

    #[Override]
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
            'chapter',
            'history',
            'page',
            'post',
        ];
        $code = $tab[array_rand($tab)];
        $tag  = new Tag();
        $tag->setTitle($generator->unique()->colorName());
        $tag->setType($code);
        $this->addReference('tag'.$code.'_'.md5(uniqid()), $tag);
        $objectManager->persist($tag);
    }
}
