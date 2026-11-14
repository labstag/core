<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\MovieCategory;
use Labstag\Entity\PageCategory;
use Labstag\Entity\PostCategory;
use Labstag\Entity\SerieCategory;
use Labstag\Entity\StoryCategory;
use Override;

class CategoryFixtures extends FixtureAbstract
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
        $tab      = [
            'movie' => MovieCategory::class,
            'page'  => PageCategory::class,
            'post'  => PostCategory::class,
            'serie' => SerieCategory::class,
            'story' => StoryCategory::class,
        ];
        foreach ($tab as $code => $class) {
            $category = new $class();
            $category->setTitle($generator->unique()->colorName());

            $parent = random_int(0, 1);
            if (1 === $parent) {
                $categories = $this->getParent('category' . $code);
                if ([] !== $categories) {
                    $parentCategory = $this->getReference(array_rand($categories), $class);
                    $category->setParent($parentCategory);
                }
            }

            $id = 'category' . $code . '_' . md5(uniqid());
            $this->addReference($id, $category);
            $this->categories[$id] = $category;
            $objectManager->persist($category);
        }
    }

    /**
     * @return mixed[]
     */
    protected function getParent(string $code): array
    {
        $tab = [];
        foreach ($this->categories as $key => $value) {
            if (0 !== substr_count((string) $key, $code)) {
                $tab[$key] = $value;
            }
        }

        return $tab;
    }
}
