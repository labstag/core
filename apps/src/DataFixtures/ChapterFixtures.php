<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Override;

class ChapterFixtures extends FixtureAbstract implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_CHAPTER = 20;

    /**
     * @var mixed[]
     */
    protected array $position = [];

    /**
     * @var Story[]
     */
    protected array $stories = [];

    /**
     * @return string[]
     */
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
        $this->loadForeach(self::NUMBER_CHAPTER, 'addChapter', $objectManager);
        $objectManager->flush();
    }

    protected function addChapter(Generator $generator, ObjectManager $objectManager): void
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

        $this->paragraphService->addParagraph($chapter, 'text');
        $this->setImage($chapter, 'imgFile');
        $this->addReference('chapter_' . md5(uniqid()), $chapter);
        $this->position[$storyId][] = $chapter;
        $objectManager->persist($chapter);
    }
}
