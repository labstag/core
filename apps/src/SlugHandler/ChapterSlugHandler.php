<?php

namespace Labstag\SlugHandler;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Labstag\Entity\Chapter;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ChapterSlugHandler implements SlugHandlerInterface
{
    public function __construct(
        private SluggableListener $sluggableListener,
    )
    {
    }

    public function handlesUrlization()
    {
        return false;
    }

    public function onChangeDecision(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug, &$needToChangeSlug): void
    {
        unset($sluggableAdapter, $config, $object, $slug);
        $needToChangeSlug = true;
    }

    public function onSlugCompletion(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug): void
    {
        unset($config);
        $objectManager    = $sluggableAdapter->getObjectManager();
        $classMetadata    = $objectManager->getClassMetadata($object::class);
        $objectRepository = $objectManager->getRepository($classMetadata->getName());
        $existingChapters = $objectRepository->findBy(
            [
                'refstory' => $object->getRefstory(),
            ],
            ['position' => 'ASC']
        );
        foreach ($existingChapters as $existingChapter) {
            if ($existingChapter === $object) {
                continue;
            }

            $newSlug = $this->setSlugForObject($objectRepository, $existingChapter);
            $existingChapter->setSlug($newSlug);
            $objectManager->persist($existingChapter);
        }

        $slug = $this->setSlugForObject($objectRepository, $object);
    }

    public function postSlugBuild(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug)
    {
        unset($sluggableAdapter, $config, $object, $slug);
    }

    public function setSlugForObject($objectRepository, $object)
    {
        $asciiSlugger        = new AsciiSlugger();
        $unicodeString       = $asciiSlugger->slug((string) $object->getTitle())->lower();
        $slug      = $unicodeString;
        $find      = false;
        $number    = 1;
        while (false === $find) {
            $testChapter = $objectRepository->findOneBy(
                [
                    'refstory' => $object->getRefstory(),
                    'slug'     => $slug,
                ]
            );
            if (!$testChapter instanceof Chapter) {
                $find = true;
                break;
            }

            if ($testChapter->getId() === $testChapter->getId()) {
                $find = true;
                break;
            }

            $slug = $unicodeString . '-' . $number;
            ++$number;
        }

        return $slug;
    }

    public static function validate(array $options, ClassMetadata $meta)
    {
        unset($options, $meta);
    }
}
