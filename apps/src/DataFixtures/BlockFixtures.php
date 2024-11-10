<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Lib\FixtureLib;
use Override;

class BlockFixtures extends FixtureLib implements DependentFixtureInterface
{
    #[Override]
    public function getDependencies(): array
    {
        return [
            DataFixtures::class,
        ];
    }

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

    private function addParagraphs(Block $block)
    {
        $generator = $this->setFaker();
        $paragraph = new Paragraph();
        $paragraph->setType('html');
        $paragraph->setTitle($generator->unique()->colorName());
        $paragraph->setContent($generator->unique()->text(1000));

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

        $this->addParagraphs($block);
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
