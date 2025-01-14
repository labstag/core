<?php

namespace Labstag\Twig\Runtime;

use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class FrontExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected SiteService $siteService,
        protected ParameterBagInterface $parameterBag,
        protected FileService $fileService,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        $mappings = $this->fileService->getMappingForEntity($entity);
        $file = '';
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($mappings as $mapping) {
            if ($field != $mapping->getFileNamePropertyName()) {
                continue;
            }

            $basePath = $this->fileService->getBasePath($entity, $mapping->getFilePropertyName());
            $content = $propertyAccessor->getValue($entity, $mapping->getFileNamePropertyName());
            if ($content != '') {
                $file = $basePath . '/' . $content;
            }
        }

        if ($file === '') {
            return 'https://picsum.photos/1200/1200?md5=' . md5((string) $entity->getId());
        }

        return $file;
    }

    public function content(?Response $response): ?string
    {
        if (!$response instanceof Response) {
            return null;
        }

        return $response->getContent();
    }
    
    /**
     * @return mixed[]
     */
    public function enable(object $entities): array
    {
        $data = [];
        foreach ($entities as $entity) {
            if ($entity->isEnable()) {
                $data[] = $entity;
            }
        }

        return $data;
    }

    /**
     * @param mixed[] $value
     */
    public function metatags(array $value): string
    {
        $metatags = $this->siteService->getMetatags($value['entity']);

        return $this->twigEnvironment->render(
            'metatags.html.twig',
            ['metatags' => $metatags]
        );
    }

    public function path(object $entity): string
    {
        $slug = $this->siteService->getSlugByEntity($entity);

        return $this->router->generate(
            'front',
            ['slug' => $slug]
        );
    }

    /**
     * @param mixed[] $data
     */
    public function title(array $data): string
    {
        $config = $this->siteService->getConfiguration();
        $siteTitle = $config->getName();
        $format = $config->getTitleFormat();
        if ($this->siteService->isHome($data)) {
            return (string) $siteTitle;
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
