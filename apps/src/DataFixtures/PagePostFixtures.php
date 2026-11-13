<?php

declare(strict_types=1);

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Paragraph\NewsListParagraph;
use Labstag\Service\ParagraphService;

class PagePostFixtures extends Fixture
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
        $post = new Page();
        $post->setPage($page);
        $post->setTitle('Posts');
        $post->setType(PageEnum::POSTS->value);
        $post->setEnable(true);

        $paragraph = $this->paragraphService->addParagraph($post, 'news-list');
        if ($paragraph instanceof NewsListParagraph) {
            $paragraph->setNbr(18);
        }

        $objectManager->persist($post);
        $objectManager->flush();
    }
}
