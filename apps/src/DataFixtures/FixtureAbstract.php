<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Youtube;
use Labstag\Entity\Category;
use Labstag\Entity\Tag;
use Labstag\Service\BlockService;
use Labstag\Service\EmailService;
use Labstag\Service\FileService;
use Labstag\Service\ParagraphService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

abstract class FixtureAbstract extends Fixture
{

    /**
     * @var Category[]
     */
    public array $categories = [];

    /**
     * @var Tag[]
     */
    public array $tags = [];

    protected int $enable;

    public function __construct(
        protected EmailService $emailService,
        protected WorkflowService $workflowService,
        protected BlockService $blockService,
        protected UserService $userService,
        protected FileService $fileService,
        protected ParagraphService $paragraphService,
    )
    {
    }

    protected function addCategoryToEntity(object $entity, string $class): void
    {
        if ([] === $this->categories) {
            return;
        }

        $max = random_int(0, count($this->categories));
        if (0 === $max) {
            return;
        }

        $categories = $this->correctionArray($this->categories);
        shuffle($categories);
        $categories = array_slice($categories, 0, $max);
        foreach ($categories as $categoryId) {
            $category = $this->getReference($categoryId, $class);
            $entity->addCategory($category);
        }
    }

    protected function addParagraphText(object $entity): void
    {
        $generator = $this->setFaker();
        $paragraph = $this->paragraphService->addParagraph($entity, 'text');
        if (is_null($paragraph)) {
            return;
        }

        $paragraph->setContent($generator->text(500));
    }

    protected function addTagToEntity(object $entity, string $class): void
    {
        if ([] === $this->tags) {
            return;
        }

        $max = random_int(0, count($this->tags));
        if (0 === $max) {
            return;
        }

        $tags = $this->correctionArray($this->tags);
        shuffle($tags);
        $tags = array_slice($tags, 0, $max);
        foreach ($tags as $tagId) {
            $tag = $this->getReference($tagId, $class);
            $entity->addTag($tag);
        }
    }

    /**
     * @return mixed[]
     */
    protected function getIdentitiesByClass(string $class): array
    {
        $data = $this->referenceRepository->getIdentitiesByClass();

        return $data[$class] ?? [];
    }

    protected function loadForeach(int $number, string $method, ObjectManager $objectManager): void
    {
        $generator    = $this->setFaker();
        $this->enable = random_int(1, $number);
        for ($index = 0; $index < $number; ++$index) {
            call_user_func([$this, $method], $generator, $objectManager, $index + 1);
        }
    }

    protected function setFaker(): Generator
    {
        $generator = Factory::create('fr_FR');
        $generator->addProvider(new FakerPicsumImagesProvider($generator));
        $generator->addProvider(new Youtube($generator));

        return $generator;
    }

    /**
     * @param mixed[]|object $entity
     */
    protected function setImage(object|array $entity, string|PropertyPathInterface $type): void
    {
        $generator = $this->setFaker();
        $filePath  = $generator->image(width: 800, height: 600);
        if (is_bool($filePath)) {
            return;
        }

        $this->fileService->setUploadedFile($filePath, $entity, $type);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function correctionArray(array $data): array
    {
        $newData = [];
        foreach (array_keys($data) as $key) {
            $newData[$key] = $key;
        }

        return $newData;
    }
}
