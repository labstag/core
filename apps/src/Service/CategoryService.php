<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Category;
use Labstag\Entity\MovieCategory;
use Labstag\Repository\CategoryRepository;

class CategoryService
{
    protected array $categories = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        protected CategoryRepository $categoryRepository,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCategoryMovieForForm(): array
    {
        $data       = $this->categoryRepository->findAllByTypeMovieEnable(MovieCategory::class);
        $categories = [];
        foreach ($data as $category) {
            $categories[$category->getTitle()] = $category->getSlug();
        }

        return $categories;
    }

    public function getType(string $title, string $class): Category
    {
        $entityRepository = $this->entityManager->getRepository($class);
        $categories       = $entityRepository->findAll();
        foreach ($categories as $category) {
            if ($category->getTitle() === $title) {
                return $category;
            }
        }

        $category = new $class();
        $category->setTitle($title);

        $this->categoryRepository->save($category);

        return $category;
    }
}
