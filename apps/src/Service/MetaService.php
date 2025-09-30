<?php

namespace Labstag\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Configuration;
use Labstag\Entity\Meta;
use Labstag\Repository\ConfigurationRepository;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Environment;

class MetaService
{

    protected ?Configuration $configuration = null;

    public function __construct(
        protected Environment $twigEnvironment,
        protected ViewResolverService $viewResolverService,
        protected ConfigurationService $configurationService,
        protected FileService $fileService,
        protected EntityManagerInterface $entityManager,
        protected ConfigurationRepository $configurationRepository,
    )
    {
    }

    public function asset(mixed $entity, string $field, bool $placeholder = true): string
    {
        $file = $this->fileService->asset($entity, $field);

        if ('' !== $file) {
            return $file;
        }

        if (!$placeholder) {
            return '';
        }

        if (!$entity instanceof Configuration) {
            $config = $this->configurationService->getConfiguration();

            return $this->asset($config, 'placeholder');
        }

        return 'https://picsum.photos/1200/1200?md5=' . md5((string) $entity->getId());
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
        $file = $this->asset($entity, 'img');
        if (null == $file) {
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
