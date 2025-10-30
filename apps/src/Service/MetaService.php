<?php

namespace Labstag\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Meta;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Environment;

final class MetaService
{
    public function __construct(
        private Environment $twigEnvironment,
        private EntityManagerInterface $entityManager,
        private ViewResolverService $viewResolverService,
        private FileService $fileService,
        private SiteService $siteService,
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

    /**
     * @return array<string, mixed>|null
     */
    public function getImageForMetatags(object $entity): ?array
    {
        $file = $this->siteService->asset($entity, 'img');
        if ('' === $file) {
            return null;
        }

        if (0 < substr_count($file, 'https://')) {
            return [
                'src'    => $file,
                'width'  => null,
                'height' => null,
                'type'   => null,
            ];
        }

        $file = str_replace('/uploads/', '', $file);
        $file = $this->fileService->getFileInAdapter('public', $file);

        if (0 < substr_count((string) $file, 'https://')) {
            return [
                'src'    => $file,
                'width'  => null,
                'height' => null,
                'type'   => null,
            ];
        }

        return $this->fileService->getInfoImage($file);
    }

    public function getMetatags(object $entity): Meta
    {
        $meta = $entity->getMeta();
        if (!$meta instanceof Meta) {
            $repository = $this->entityManager->getRepository($entity::class);
            $meta       = new Meta();
            $entity->setMeta($meta);
            $repository->save($entity);
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
