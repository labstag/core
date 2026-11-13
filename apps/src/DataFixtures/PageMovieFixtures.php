<?php

declare(strict_types=1);

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Paragraph\MovieParagraph;
use Labstag\Service\ParagraphService;

class PageMovieFixtures extends Fixture
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
        $movies = new Page();
        $movies->setPage($page);
        $movies->setTitle('Mes derniers films vus');
        $movies->setType(PageEnum::MOVIES->value);
        $movies->setEnable(true);

        $paragraph = $this->paragraphService->addParagraph($movies, 'movie');
        if ($paragraph instanceof MovieParagraph) {
            $paragraph->setNbr(18);
        }

        $objectManager->persist($movies);
        $objectManager->flush();
    }
}
