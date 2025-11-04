<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\LinksBlock;
use Labstag\Entity\Page;
use Override;

class BlockFixtures extends FixtureAbstract implements DependentFixtureInterface
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

    private function addLinksFooter1(LinksBlock $linksBlock): void
    {
        $data          = $linksBlock->getData();
        $data['links'] = [];
        $page          = $this->getPageByTitle('Contact');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Plan du site');
        $this->setLink($page, $data);

        $linksBlock->setData($data);
    }

    private function addLinksFooter2(LinksBlock $linksBlock): void
    {
        $data          = $linksBlock->getData();
        $data['links'] = [];
        $page          = $this->getPageByTitle('Mentions légales');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Données personnelles');
        $this->setLink($page, $data);
        $linksBlock->setData($data);
    }

    private function addLinksHeader(LinksBlock $linksBlock): void
    {
        $data          = $linksBlock->getData();
        $data['links'] = [];
        $page          = $this->getPageByTitle('Posts');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Mes étoiles github');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Mes derniers films vus');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Mes séries favorites');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Histoires');
        $this->setLink($page, $data);
        $page = $this->getPageByTitle('Mon parcours pro');
        $this->setLink($page, $data);
        $linksBlock->setData($data);
    }

    private function addParagraphsHead(Block $block): void
    {
        $this->paragraphService->addParagraph($block, 'head-story');
        $this->paragraphService->addParagraph($block, 'head-serie');
        $this->paragraphService->addParagraph($block, 'head-post');
        $this->paragraphService->addParagraph($block, 'head-chapter');
        $this->paragraphService->addParagraph($block, 'head-season');
        $this->paragraphService->addParagraph($block, 'head-saga');
        $this->paragraphService->addParagraph($block, 'chapter-list');
        $this->paragraphService->addParagraph($block, 'season-list');
        $this->paragraphService->addParagraph($block, 'episode-list');
        $this->paragraphService->addParagraph($block, 'saga-list');
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
        $block = $this->newBlock('admin');
        $block->setRegion('header');
        $block->setTitle('Header admin');
        $block->setRoles(['ROLE_ADMIN']);
        yield $block;

        $block = $this->newBlock('links');
        $block->setTitle('Header Link');
        $block->setRegion('header');
        $block->setClasses('headerlink_principal');
        $this->addLinksHeader($block);
        yield $block;

        $block = $this->newBlock('breadcrumb');
        $block->setRegion('header');
        $block->setTitle('Header breadcrumb');
        yield $block;

        $block = $this->newBlock('hero');
        $block->setRegion('main');
        $block->setTitle('Main Hero');
        yield $block;

        $block = $this->newBlock('flashbag');
        $block->setRegion('main');
        $block->setTitle('Main Flashbag');
        yield $block;

        $block = $this->newBlock('paragraphs');
        $block->setRegion('main');
        $block->setTitle('Main Content');
        $this->addParagraphsHead($block);
        yield $block;

        $block = $this->newBlock('html');
        $block->setRegion('main');
        $block->setTitle('Main HTML');
        yield $block;

        $block = $this->newBlock('content');
        $block->setRegion('main');
        $block->setTitle('Main Content');
        $this->addParagraphsTest($block);
        yield $block;

        $block = $this->newBlock('paragraphs');
        $block->setRegion('main');
        $block->setTitle('Main Paragraphs');

        $this->addParagraphsTest($block);
        yield $block;

        $block = $this->newBlock('html');
        $block->setRegion('footer');
        $block->setTitle('Footer HTML');
        yield $block;

        $block = $this->newBlock('links');
        $block->setRegion('footer');
        $block->setTitle('Footer Link');
        $this->addLinksFooter1($block);
        yield $block;

        $block = $this->newBlock('links');
        $block->setRegion('footer');
        $block->setTitle('Footer Link');
        $this->addLinksFooter2($block);
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

    private function newBlock(?string $code): Block
    {
        $block = $this->blockService->getByCode($code);
        if (is_null($block)) {
            throw new Exception('Block ' . $code . ' not found');
        }

        $blockClass = $block->getClass();

        return new $blockClass();
    }

    private function setLink(?Page $page, array &$data): void
    {
        if (!$page instanceof Page) {
            return;
        }

        $data['links'][] = [
            'title'   => $page->getTitle(),
            'url'     => '[pageurl:' . $page->getId() . ']',
            'classes' => null,
            'blank'   => false,
        ];
    }
}
