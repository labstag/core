<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Entity\Tag;
use Labstag\Lib\FixtureLib;
use Override;

class ChapterFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_CHAPTER = 50;

    protected array $histories = [];

    protected array $position = [];

    #[Override]
    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            HistoryFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->histories = $this->getIdentitiesByClass(History::class);
        $this->tags      = $this->getIdentitiesByClass(Tag::class, 'chapter');
        $this->loadForeach(self::NUMBER_CHAPTER, 'addChapter', $objectManager);
        $objectManager->flush();
    }

    protected function addChapter(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $historyId = array_rand($this->histories);
        if (!isset($this->position[$historyId])) {
            $this->position[$historyId] = [];
        }

        $chapter = new Chapter();
        $chapter->setEnable((bool) random_int(0, 1));
        $chapter->setPosition(count($this->position[$historyId]) + 1);

        $history     = $this->getReference($historyId, History::class);
        $dateHistory = $history->getCreatedAt();
        $chapter->setResume($generator->unique()->text(200));
        $chapter->setCreatedAt($generator->unique()->dateTimeBetween($dateHistory, '+ 1 month'));
        $chapter->setRefhistory($history);
        $chapter->setTitle($generator->unique()->colorName());
        $this->setImage($chapter, 'imgFile');
        $this->addTagToEntity($chapter);
        $this->addReference('chapter_'.md5(uniqid()), $chapter);
        $this->position[$historyId][] = $chapter;
        $objectManager->persist($chapter);
    }
}
