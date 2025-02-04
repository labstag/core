<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Link;
use Labstag\Entity\Page;
use Labstag\Lib\FixtureLib;
use Override;

class BlockFixtures extends FixtureLib implements DependentFixtureInterface
{

    /**
     * @var Page[]
     */
    protected array $pages = [];

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [PageFixtures::class];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->pages = $this->getIdentitiesByClass(Page::class);
        $data        = $this->data();
        foreach ($data as $row) {
            $row->setEnable(true);
            $objectManager->persist($row);
        }

        $objectManager->flush();
    }

    private function addLinksHeader(Block $block): void
    {
        $page = $this->getPageByTitle('Posts');
        if (!is_null($page)) {
            $link = new Link();
            $link->setTitle($page->getTitle());
            $link->setUrl('[page:' . $page->getId() . ']');
            $block->addLink($link);
        }

        $page = $this->getPageByTitle('Mes étoiles github');
        if (!is_null($page)) {
            $link = new Link();
            $link->setTitle($page->getTitle());
            $link->setUrl('[page:' . $page->getId() . ']');
            $block->addLink($link);
        }

        $page = $this->getPageByTitle('Mes derniers films vus');
        if (!is_null($page)) {
            $link = new Link();
            $link->setTitle($page->getTitle());
            $link->setUrl('[page:' . $page->getId() . ']');
            $block->addLink($link);
        }

        $page = $this->getPageByTitle('Histoires');
        if (!is_null($page)) {
            $link = new Link();
            $link->setTitle($page->getTitle());
            $link->setUrl('[page:' . $page->getId() . ']');
            $block->addLink($link);
        }
    }

    private function addLinksFooter(Block $block): void
    {
        $page = $this->getPageByTitle('Contact');
        if (!is_null($page)) {
            $link = new Link();
            $link->setTitle($page->getTitle());
            $link->setUrl('[page:' . $page->getId() . ']');
            $block->addLink($link);
        }

        $page = $this->getPageByTitle('Plan du site');
        if (!is_null($page)) {
            $link = new Link();
            $link->setTitle($page->getTitle());
            $link->setUrl('[page:' . $page->getId() . ']');
            $block->addLink($link);
        }
    }

    private function addParagraphsHead(Block $block): void
    {
        $this->paragraphService->addParagraph($block, 'head-story');
        $this->paragraphService->addParagraph($block, 'head-post');
        $this->paragraphService->addParagraph($block, 'head-chapter');
        $this->paragraphService->addParagraph($block, 'chapter-list');
    }

    private function addParagraphsTest(Block $block): void
    {
        $this->paragraphService->addParagraph($block, 'chapter-lastnext');
    }

    /**
     * @return Generator<Block>
     */
    private function data(): Generator
    {
        $block = new Block();
        $block->setRegion('header');
        $block->setTitle('Header admin');
        $block->setRoles(['ROLE_ADMIN']);
        $block->setType('admin');
        yield $block;

        $block = new Block();
        $block->setRegion('header');
        $block->setTitle('Header Link');
        $block->setType('links');
        $this->addLinksHeader($block);
        yield $block;

        $block = new Block();
        $block->setRegion('header');
        $block->setTitle('Header breadcrumb');
        $block->setType('breadcrumb');
        yield $block;

        $block = new Block();
        $block->setRegion('main');
        $block->setTitle('Main Hero');
        $block->setType('hero');
        yield $block;

        $block = new Block();
        $block->setRegion('main');
        $block->setTitle('Main Flashbag');
        $block->setType('flashbag');
        yield $block;

        $block = new Block();
        $block->setRegion('main');
        $block->setTitle('Main Content');
        $block->setType('paragraphs');
        $this->addParagraphsHead($block);
        yield $block;

        $block = new Block();
        $block->setRegion('main');
        $block->setTitle('Main HTML');
        $block->setType('html');
        yield $block;

        $block = new Block();
        $block->setRegion('main');
        $block->setTitle('Main Content');
        $block->setType('content');

        $this->addParagraphsTest($block);
        yield $block;

        $block = new Block();
        $block->setRegion('main');
        $block->setTitle('Main Paragraphs');
        $block->setType('paragraphs');

        $this->addParagraphsTest($block);
        yield $block;

        $block = new Block();
        $block->setRegion('footer');
        $block->setTitle('Footer HTML');
        $block->setType('html');
        yield $block;

        $block = new Block();
        $block->setRegion('footer');
        $block->setTitle('Footer Link');
        $block->setType('links');
        $this->addLinksFooter($block);
        yield $block;
    }

    private function getPageByTitle(string $pageTitle): ?Page
    {
        $page = null;
        foreach (array_keys($this->pages) as $pageId) {
            $data = $this->getReference($pageId, Page::class);
            if ($pageTitle != $data->getTitle()) {
                continue;
            }

            $page = $data;

            break;
        }

        return $page;
    }
}
