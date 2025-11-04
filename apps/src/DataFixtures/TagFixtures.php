<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\PageTag;
use Labstag\Entity\PostTag;
use Labstag\Entity\StoryTag;
use Override;

class TagFixtures extends FixtureAbstract
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

    protected function addTag(Generator $generator, ObjectManager $objectManager): void
    {
        $tab  = [
            'page'  => PageTag::class,
            'post'  => PostTag::class,
            'story' => StoryTag::class,
        ];
        foreach ($tab as $code => $class) {
            $tag = new $class();
            $tag->setTitle($generator->unique()->colorName());
            $this->addReference('tag' . $code . '_' . md5(uniqid()), $tag);
            $objectManager->persist($tag);
        }
    }
}
