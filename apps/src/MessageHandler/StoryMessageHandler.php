<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Story;
use Labstag\Message\StoryMessage;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\StoryRepository;
use Labstag\Service\StoryService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsMessageHandler]
final class StoryMessageHandler
{
    public function __construct(
        private StoryService $storyService,
        private MessageBusInterface $messageBus,
        private StoryRepository $storyRepository,
        private ChapterRepository $chapterRepository,
    )
    {
    }

    public function __invoke(StoryMessage $storyMessage): void
    {
        $storyId = $storyMessage->getData();
        $story   = $this->storyRepository->find($storyId);
        if (!$story instanceof Story) {
            return;
        }

        foreach ($story->getChapters() as $chapter) {
            $this->correctionSlug($chapter);
        }

        // $this->storyService->setPdf($story);

        $this->storyRepository->save($story);
    }

    private function correctionSlug(object $chapter): void
    {
        $asciiSlugger  = new AsciiSlugger();
        $unicodeString = $asciiSlugger->slug((string) $chapter->getTitle())->lower();
        $slug      = $unicodeString;
        $find      = false;
        $number    = 1;
        while (false === $find) {
            $testChapter = $this->chapterRepository->findOneBy(
                [
                    'refstory' => $chapter->getRefstory(),
                    'slug'     => $slug,
                ]
            );
            if (!$testChapter instanceof Story) {
                $find = true;
                $chapter->setSlug($slug);
                break;
            }

            if ($testChapter->getId() === $chapter->getId()) {
                $find = true;
                break;
            }

            $slug = $unicodeString . '-' . $number;
            ++$number;
        }

        $this->chapterRepository->save($chapter);
    }
}
