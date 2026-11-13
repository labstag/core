<?php

declare(strict_types=1);

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Service\ParagraphService;

class PageGameFixtures extends Fixture
{
    public function __construct(
        private ParagraphService $paragraphService,
    )
    {
    }

    public function getDependencies(): array
    {
        return [PageFixtures::class];
    }

    public function load(ObjectManager $objectManager): void
    {
        $page = $this->getReference(PageFixtures::PAGE_HOME, Page::class);
        $game = new Page();
        $game->setPage($page);
        $game->setTitle('Ma ludothèque');
        $game->setType(PageEnum::GAMES->value);
        $game->setEnable(true);

        $this->paragraphService->addParagraph($game, 'game');

        $objectManager->persist($game);
        $objectManager->flush();
    }
}
