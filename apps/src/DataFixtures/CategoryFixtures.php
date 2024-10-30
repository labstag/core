<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Lib\FixtureLib;

class CategoryFixtures extends FixtureLib
{
    /**
     * @var int
     */
    protected const NUMBER_CATEGORY = 30;

    protected array $categories = [];


    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_CATEGORY, 'addCategory', $objectManager);
        $objectManager->flush();
    }

    protected function addCategory(
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $tab = [
            'history', 'page', 'post'
        ];
        $code = $tab[array_rand($tab)];
        $category = new Category();
        $category->setTitle($generator->unique()->colorName());
        $category->setType($code);
        $parent = rand(0, 1);
        if ($parent == 1) {
            $categories = $this->getParent('category'.$code);
            if (count($categories) != 0) {
                $parentCategory = $this->getReference(array_rand($categories));
                $category->setParent($parentCategory);
            }
        }

        $id = 'category'.$code.'_'.md5(uniqid());
        $this->addReference($id, $category);
        $this->categories[$id] = $category;
        $objectManager->persist($category);
        
    }

    protected function getParent(string $code): array
    {
        $tab = [];
        foreach ($this->categories as $key => $value) {
            if (substr_count($key, $code) != 0) {
                $tab[$key] = $value;
            }
        }

        return $tab;
    }
}
