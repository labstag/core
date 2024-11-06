<?php

namespace Labstag\DataFixtures;

use Override;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Block;
use Labstag\Lib\FixtureLib;

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

    private function data(): iterable
    {
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
