<?php

namespace Labstag\Twig\Runtime;

use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

class FrontExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected SiteService $siteService,
        protected ParameterBagInterface $parameterBag,
        protected FileService $fileService
    )
    {
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

        if ('' === $file) {
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

    public function enable($entities)
    {
        $data = [];
        foreach ($entities as $entity) {
            if ($entity->isEnable()) {
                $data[] = $entity;
            }
        }

        return $data;
    }

    public function metatags($value)
    {
        // TODO
        unset($value);
        // ...
    }

    public function path($entity)
    {
        $slug = $this->siteService->getSlugByEntity($entity);

        return $this->router->generate('front', ['slug' => $slug]);
    }

    public function title($data)
    {
        $config    = $this->siteService->getConfiguration();
        $siteTitle = $config['site_name'];
        $format    = $config['title_format'];
        if ($this->siteService->isHome($data)) {
            return $siteTitle;
        }

        return str_replace(
            [
                '%content_title%',
                '%site_name%',
            ],
            [
                $this->siteService->setTitle($data['entity']),
                $siteTitle,
            ],
            $format
        );
    }
}
