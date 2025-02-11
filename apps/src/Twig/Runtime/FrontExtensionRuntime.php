<?php

namespace Labstag\Twig\Runtime;

use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class FrontExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected RouterInterface $router,
        protected SiteService $siteService,
        protected ParameterBagInterface $parameterBag,
        protected FileService $fileService,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function asset(mixed $entity, string $field, bool $placeholder = true): string
    {
        return $this->siteService->asset($entity, $field, $placeholder);
    }

    public function tarteaucitron(): string
    {
        $config = $this->siteService->getConfiguration();
        if (empty(trim($config->getTacServices()))) {
            return '';
        }

        return $this->twigEnvironment->render(
            'tarteaucitron.html.twig',
            [
                'config'   => $config,
                'services' => $config->getTacServices(),
            ]
        );
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
        $entity   = $value['entity'];
        $metatags = $this->siteService->getMetatags($entity);
        $image    = $this->siteService->getImageForMetatags($entity);
        $config   = $this->siteService->getConfiguration();
        $favicon  = $this->siteService->getFavicon();

        return $this->twigEnvironment->render(
            'metatags.html.twig',
            [
                'url'      => $config->getUrl(),
                'favicon'  => $favicon,
                'image'    => $image,
                'entity'   => $entity,
                'metatags' => $metatags,
            ]
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
        $request   = $this->requestStack->getCurrentRequest();
        $config    = $this->siteService->getConfiguration();
        $siteTitle = $config->getName();
        $format    = $config->getTitleFormat();
        if ($this->siteService->isHome($data)) {
            return (string) $siteTitle;
        }

        $contentTitle = $this->siteService->setTitle($data['entity']);
        $page         = $request->attributes->getInt('page', 1);
        if (1 != $page) {
            $contentTitle .= ' - Page ' . $page;
        }

        return str_replace(['%content_title%', '%site_name%'], [$contentTitle, $siteTitle], $format);
    }
}
