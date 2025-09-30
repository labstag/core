<?php

namespace Labstag\Service;

use DateTime;
use Labstag\Entity\Meta;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Environment;

final class MetaService
{
    public function __construct(
        private Environment $twigEnvironment,
        private ViewResolverService $viewResolverService,
        private FileService $fileService,
    )
    {
    }

    public function getEntityParent(?Meta $meta): ?object
    {
        if (!$meta instanceof Meta) {
            return null;
        }

        $return = new stdClass();

        $return->name  = null;
        $return->value = null;

        $reflectionClass  = new ReflectionClass($meta);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name  = $reflectionProperty->getName();
            $value = $propertyAccessor->getValue($meta, $name);
            if (!is_object($value)) {
                continue;
            }

            if ($value instanceof DateTime) {
                continue;
            }

            $return->name  = $name;
            $return->value = $value;
        }

        return $return;
    }

    public function getImageForMetatags(mixed $entity): ?array
    {
        $file = $this->fileService->asset($entity, 'img');
        if ('' === $file) {
            return null;
        }

        $file = str_replace('/uploads/', '', $file);
        $file = $this->fileService->getFileInAdapter('public', $file);

        return $this->fileService->getInfoImage($file);
    }

    public function getMetatags(object $entity): Meta
    {
        $meta = $entity->getMeta();
        if (!$meta instanceof Meta) {
            $meta = new Meta();
        }

        if (!is_null($meta->getDescription()) && '' !== $meta->getDescription()) {
            return $meta;
        }

        $html = $this->twigEnvironment->render(
            'metagenerate.html.twig',
            $this->viewResolverService->getDataByEntity($entity, true)
        );

        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
        $text = mb_substr($text, 0, 256);

        $meta->setDescription($text);

        return $meta;
    }
}
