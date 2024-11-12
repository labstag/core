<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
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

    private function addParagraphsHead(Block $block)
    {
        $paragraph = new Paragraph();
        $paragraph->setType('head-history');

        $block->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setType('head-post');

        $block->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setType('head-chapter');

        $block->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setType('chapter-list');

        $block->addParagraph($paragraph);

        $paragraph = new Paragraph();
        $paragraph->setType('chapter-info');

        $block->addParagraph($paragraph);
    }

    private function addParagraphsTest(Block $block)
    {
        $paragraph = new Paragraph();
        $paragraph->setType('chapter-lastnext');

        $block->addParagraph($paragraph);
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
        yield $block;

        $block = new Block();
        $block->setRegion('header');
        $block->setTitle('Header breadcrumb');
        $block->setType('breadcrumb');
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
        yield $block;
    }
}
