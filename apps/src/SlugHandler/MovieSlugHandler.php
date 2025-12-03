<?php

namespace Labstag\SlugHandler;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Symfony\Component\String\Slugger\AsciiSlugger;

class MovieSlugHandler implements SlugHandlerInterface
{
    public function __construct(
        private SluggableListener $sluggableListener,
    )
    {
    }

    public function handlesUrlization(): bool
    {
        return false;
    }

    public function onChangeDecision(
        SluggableAdapter $sluggableAdapter,
        array &$config,
        $object,
        &$slug,
        &$needToChangeSlug,
    ): void
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

        $asciiSlugger = new AsciiSlugger();
        $slug         = $asciiSlugger->slug((string) $object->getTitle())->lower();
        if (preg_match('/^\d+$/', $slug)) {
            $slug .= '-movie';
        }

        $originalSlug = $slug;

        $existingMovies = $objectRepository->findBy(
            [
                'title' => $object->getTitle(),
            ]
        );

        foreach ($existingMovies as $existingMovie) {
            if ($existingMovie === $object) {
                continue;
            }

            $date    = $object->getReleaseDate();
            $newSlug = $date ? $originalSlug . '-' . $date->format('Y') : $originalSlug . '-' . uniqid();
            $existingMovie->setSlug($newSlug);
            $objectManager->persist($existingMovie);
        }

        $date = $object->getReleaseDate();
        $slug = $date ? $originalSlug . '-' . $date->format('Y') : $originalSlug . '-' . uniqid();
    }

    public function postSlugBuild(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug): void
    {
        unset($sluggableAdapter, $config, $object, $slug);
    }

    public static function validate(array $options, ClassMetadata $meta): void
    {
        unset($options, $meta);
    }
}
