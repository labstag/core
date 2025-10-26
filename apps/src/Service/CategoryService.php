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
        $this->setByTypes($type);
        foreach ($this->categories[$type] as $category) {
            if ($category->getTitle() === $title) {
                return $category;
            }
        }

        $category = new Category();
        $category->setTitle($title);
        $category->setType($type);

        $this->categoryRepository->save($category);
        $this->categories[$type][] = $category;

        return $category;
    }

    private function setByTypes(string $type): void
    {
        if (isset($this->categories[$type])) {
            return;
        }

        $this->categories[$type] = $this->categoryRepository->findBy(
            ['type' => $type]
        );
    }
}
