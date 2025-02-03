<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Lib\FixtureLib;
use Override;

class CategoryFixtures extends FixtureLib
{
    /**
     * @var int
     */
    protected const NUMBER_CATEGORY = 30;

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_CATEGORY, 'addCategory', $objectManager);
        $objectManager->flush();
    }

    protected function addCategory(Generator $generator, ObjectManager $objectManager): void
    {
        $tab      = ['story', 'page', 'post'];
        $code     = $tab[array_rand($tab)];
        $category = new Category();
        $category->setTitle($generator->unique()->colorName());
        $category->setType($code);

        $parent = random_int(0, 1);
        if (1 == $parent) {
            $categories = $this->getParent('category' . $code);
            if (0 != count($categories)) {
                $parentCategory = $this->getReference(array_rand($categories), Category::class);
                $category->setParent($parentCategory);
            }
        }

        $id = 'category' . $code . '_' . md5(uniqid());
        $this->addReference($id, $category);
        $this->categories[$id] = $category;
        $objectManager->persist($category);
    }

    /**
     * @return mixed[]
     */
    protected function getParent(string $code): array
    {
        $tab = [];
        foreach ($this->categories as $key => $value) {
            if (0 != substr_count($key, $code)) {
                $tab[$key] = $value;
            }
        }

        return $tab;
    }
}
