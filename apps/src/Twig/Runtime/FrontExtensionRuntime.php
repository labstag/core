<?php

namespace Labstag\Twig\Runtime;

use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\RuntimeExtensionInterface;

class FrontExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected SiteService $siteService,
        protected ParameterBagInterface $parameterBag,
        protected FileService $fileService
    )
    {
        // Inject dependencies if needed
    }

    public function asset($entity, $field)
    {
        $mappings         = $this->fileService->getMappingForEntity($entity);
        $file             = '';
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($mappings as $mapping) {
            if ($field != $mapping->getFileNamePropertyName()) {
                continue;
            }

            $basePath = $this->fileService->getBasePath($entity, $mapping->getFilePropertyName());
            $content  = $propertyAccessor->getValue($entity, $mapping->getFileNamePropertyName());
            if ('' != $content) {
                $file = $basePath.'/'.$content;
            }
        }

        if ('' === $file || 'dev' == $this->parameterBag->get('kernel.environment')) {
            return 'https://picsum.photos/1200/1200?md5='.md5((string) $entity->getId());
        }

        return $file;
    }

    public function content($content)
    {
        if (is_null($content)) {
            return null;
        }

        return $content->getContent();
    }

    public function metatags($value)
    {
        // TODO
        unset($value);
        // ...
    }

    public function path($entity)
    {
        return $this->siteService->getSlugByEntity($entity);
    }

    public function title($value)
    {
        // TODO
        unset($value);

        return 'Welcome !';
    }
}
