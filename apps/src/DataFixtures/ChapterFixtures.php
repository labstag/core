<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Labstag\Entity\Tag;
use Labstag\Lib\FixtureLib;
use Override;

class ChapterFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_CHAPTER = 50;

    protected array $position = [];

    protected array $stories = [];

    #[Override]
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            StoryFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->stories = $this->getIdentitiesByClass(Story::class);
        $this->tags    = $this->getIdentitiesByClass(Tag::class, 'chapter');
        $this->loadForeach(self::NUMBER_CHAPTER, 'addChapter', $objectManager);
        $objectManager->flush();
    }

    protected function addChapter(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $storyId = array_rand($this->stories);
        if (!isset($this->position[$storyId])) {
            $this->position[$storyId] = [];
        }

        $chapter = new Chapter();
        $chapter->setEnable((bool) random_int(0, 1));
        $chapter->setPosition(count($this->position[$storyId]) + 1);

        $story     = $this->getReference($storyId, Story::class);
        $dateStory = $story->getCreatedAt();
        $chapter->setResume($generator->unique()->text(200));
        $chapter->setCreatedAt($generator->unique()->dateTimeBetween($dateStory, '+ 1 month'));
        $chapter->setRefstory($story);
        $chapter->setTitle($generator->unique()->colorName());
        $this->addParagraphText($chapter);
        $this->setImage($chapter, 'imgFile');
        $this->addTagToEntity($chapter);
        $this->addReference('chapter_'.md5(uniqid()), $chapter);
        $this->position[$storyId][] = $chapter;
        $objectManager->persist($chapter);
    }
}
