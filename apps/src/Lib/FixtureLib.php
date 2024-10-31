<?php

namespace Labstag\Lib;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Tag;

abstract class FixtureLib extends Fixture
{

    public array $categories = [];

    public array $tags = [];

    protected function addCategoryToEntity($entity)
    {
        if ((!property_exists($this, 'categories') || null === $this->categories) && 0 != count($this->categories)) {
            return;
        }

        $max = random_int(0, count($this->categories));
        if (0 == $max) {
            return;
        }

        $categories = $this->correctionArray($this->categories);
        shuffle($categories);
        $categories = array_slice($categories, 0, $max);
        foreach ($categories as $categoryId) {
            $category = $this->getReference($categoryId, Category::class);
            $entity->addCategory($category);
        }
    }

    protected function addTagToEntity($entity)
    {
        if ((!property_exists($this, 'tags') || null === $this->tags) && 0 != count($this->tags)) {
            return;
        }

        $max = random_int(0, count($this->tags));
        if (0 == $max) {
            return;
        }

        $tags = $this->correctionArray($this->tags);
        shuffle($tags);
        $tags = array_slice($tags, 0, $max);
        foreach ($tags as $tagId) {
            $tag = $this->getReference($tagId, Tag::class);
            $entity->addTag($tag);
        }
    }

    protected function getIdentitiesByClass(string $class, ?string $id = null): array
    {
        $data = $this->referenceRepository->getIdentitiesByClass();

        $data = $data[$class] ?? [];

        if (null !== $id) {
            $data = array_filter(
                $data,
                fn ($key) => str_contains((string) $key, $id),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $data;
    }

    protected function loadForeach(
        int $number,
        string $method,
        ObjectManager $objectManager
    ): void
    {
        $generator = $this->setFaker();
        for ($index = 0; $index < $number; ++$index) {
            call_user_func([$this, $method], $generator, $objectManager);
        }
    }

    protected function setFaker(): Generator
    {
        return Factory::create('fr_FR');
    }

    private function correctionArray($data)
    {
        $newData = [];
        foreach (array_keys($data) as $key) {
            $newData[$key] = $key;
        }

        return $newData;
    }
}
