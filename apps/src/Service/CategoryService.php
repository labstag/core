<?php

namespace Labstag\Service;

use Labstag\Entity\Category;
use Labstag\Repository\CategoryRepository;

class CategoryService
{

    protected array $categories = [];

    public function __construct(
        protected CategoryRepository $categoryRepository,
    )
    {
    }

    public function getType(string $type, string $title): Category
    {
        $categories = $this->categoryRepository->findBy(
            ['type' => $type]
        );
        foreach ($categories as $category) {
            if ($category->getTitle() === $title) {
                return $category;
            }
        }

        $category = new Category();
        $category->setTitle($title);
        $category->setType($type);

        $this->categoryRepository->save($category);

        return $category;
    }
}
