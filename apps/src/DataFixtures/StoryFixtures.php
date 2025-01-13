<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Meta;
use Labstag\Entity\Story;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Override;

class StoryFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_HISTORY = 50;

    protected array $users = [];

    #[Override]
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->users = $this->getIdentitiesByClass(User::class);
        $this->tags = $this->getIdentitiesByClass(Tag::class, 'story');
        $this->categories = $this->getIdentitiesByClass(Category::class, 'story');
        $this->loadForeach(self::NUMBER_HISTORY, 'addStory', $objectManager);
        $objectManager->flush();
    }

    protected function addStory(Generator $generator, ObjectManager $objectManager): void
    {
        $meta = new Meta();
        $story = new Story();
        $story->setCreatedAt($generator->unique()->dateTimeBetween('- 8 month', 'now'));
        $story->setMeta($meta);
        $story->setResume($generator->unique()->text(200));
        $story->setEnable((bool) random_int(0, 1));
        $story->setRefuser($this->getReference(array_rand($this->users), User::class));
        $story->setTitle($generator->unique()->colorName());
        $this->addParagraphText($story);
        $this->setImage($story, 'imgFile');
        $this->addTagToEntity($story);
        $this->addCategoryToEntity($story);
        $this->addReference('story_' . md5(uniqid()), $story);
        $objectManager->persist($story);
    }
}
