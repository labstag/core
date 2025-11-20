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

    public function handlesUrlization()
    {
        return false;
    }

    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug): void
    {
        $needToChangeSlug = true;
    }

    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug): void
    {
        $objectManager    = $ea->getObjectManager();
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

        if (0 < count($existingMovies)) {
            $date = $object->getReleaseDate();
            $slug = $date ? $originalSlug . '-' . $date->format('Y') : $originalSlug . '-' . uniqid();
        } else {
            $slug = $originalSlug;
        }
    }

    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    public static function validate(array $options, ClassMetadata $meta)
    {
    }
}
