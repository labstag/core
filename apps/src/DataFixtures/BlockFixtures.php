<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Block;
use Labstag\Entity\Link;
use Labstag\Lib\FixtureLib;
use Override;

class BlockFixtures extends FixtureLib
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $data = $this->data();
        foreach ($data as $row) {
            $row->setEnable(true);
            $objectManager->persist($row);
        }

        $objectManager->flush();
    }

    private function addLinks(Block $block)
    {
        $generator = $this->setFaker();

        $count = random_int(1, 5);
        for ($i = 1; $i <= $count; ++$i) {
            $link = new Link();
            $link->setTitle($generator->sentence(1));
            $link->setUrl($generator->url);
            $link->setBlank($generator->boolean);
            $block->addLink($link);
        }
    }

    private function addParagraphsHead(Block $block)
    {
        $this->paragraphService->addParagraph($block, 'head-story');
        $this->paragraphService->addParagraph($block, 'head-post');
        $this->paragraphService->addParagraph($block, 'head-chapter');
        $this->paragraphService->addParagraph($block, 'chapter-list');
    }

    private function addParagraphsTest(Block $block)
    {
        $this->paragraphService->addParagraph($block, 'chapter-lastnext');
    }

    private function data(): iterable
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
        $this->addLinks($block);
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
        $this->addLinks($block);
        yield $block;
    }
}
