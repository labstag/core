<?php

namespace Labstag\SlugHandler;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Labstag\Entity\Saga;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SagaSlugHandler implements SlugHandlerInterface
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
        $objectRepository = $objectManager->getRepository(Saga::class);

        $asciiSlugger = new AsciiSlugger();
        $slug         = $asciiSlugger->slug((string) $object->getTitle())->lower();

        $originalSlug = $slug;

        $existingMovies = $objectRepository->findBy(
            ['slug' => $slug]
        );

        if ($existingMovies) {
            $slug = $originalSlug . '-saga';
        }
    }

    public function postSlugBuild(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug)
    {
        unset($sluggableAdapter, $config, $object, $slug);
    }

    public static function validate(array $options, ClassMetadata $meta)
    {
        unset($options, $meta);
    }
}
