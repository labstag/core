<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Lib\FixtureLib;
use Override;

class ChapterFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_CHAPTER = 50;

    protected array $position = [];

    #[Override]
    public function getDependencies(): array
    {
        return [
            HistoryFixtures::class,
        ];
    }
    
    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_CHAPTER, 'addChapter', $objectManager);
        $objectManager->flush();
    }

    protected function addChapter(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $histories = $this->getIdentitiesByClass(History::class);
        $historyId = array_rand($histories);
        if (!isset($this->position[$historyId])) {
            $this->position[$historyId] = [];
        }

        $chapter = new Chapter();
        $chapter->setEnable((bool) rand(0, 1));
        $chapter->setPosition(count($this->position[$historyId])+1);
        $history = $this->getReference($historyId);
        $chapter->setRefhistory($history);
        $chapter->setTitle($generator->unique()->colorName());
        $this->addReference('chapter_'.md5(uniqid()), $chapter);
        $this->position[$historyId][] = $chapter;
        $objectManager->persist($chapter);
    }
}
