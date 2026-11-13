<?php

declare(strict_types=1);

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Paragraph\SerieParagraph;
use Labstag\Service\ParagraphService;

class PageSerieFixtures extends Fixture
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
        $page   = $this->getReference(PageFixtures::PAGE_HOME, Page::class);
        $series = new Page();
        $series->setPage($page);
        $series->setTitle('Mes séries favorites');
        $series->setType(PageEnum::SERIES->value);
        $series->setEnable(true);

        $paragraph = $this->paragraphService->addParagraph($series, 'serie');
        if ($paragraph instanceof SerieParagraph) {
            $paragraph->setNbr(18);
        }

        $objectManager->persist($series);
        $objectManager->flush();
    }
}
