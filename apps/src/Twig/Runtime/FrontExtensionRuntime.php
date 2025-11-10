<?php

namespace Labstag\Twig\Runtime;

use DOMDocument;
use Essence\Essence;
use Essence\Media;
use Labstag\Service\BlockService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\MetaService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
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
        protected MetaService $metaService,
        protected SlugService $slugService,
        protected RouterInterface $router,
        protected ConfigurationService $configurationService,
        protected SiteService $siteService,
        protected ParameterBagInterface $parameterBag,
        protected FileService $fileService,
        protected BlockService $blockService,
        protected ParagraphService $paragraphService,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function asset(mixed $entity, string $field, bool $placeholder = true): string
    {
        return $this->siteService->asset($entity, $field, $placeholder);
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
        $metatags = $this->metaService->getMetatags($entity);
        $image    = $this->metaService->getImageForMetatags($entity);
        $config   = $this->configurationService->getConfiguration();
        $favicon  = $this->siteService->getFileFavicon();
        $jsonLd   = $this->metaService->getJsonLd($entity);

        return $this->twigEnvironment->render(
            'metatags.html.twig',
            [
                'jsonLd'   => $jsonLd,
                'url'      => $config->getUrl(),
                'favicon'  => $favicon,
                'image'    => $image,
                'entity'   => $entity,
                'metatags' => $metatags,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function oembed(string $url): array
    {
        $essence = new Essence();

        // Load any url:
        $media = $essence->extract(
            $url,
            [
                'maxwidth'  => 800,
                'maxheight' => 600,
            ]
        );
        if (!$media instanceof Media) {
            return [];
        }

        $html   = $media->has('html') ? $media->get('html') : '';
        $oembed = $this->getOEmbedUrl($html);
        if (is_null($oembed)) {
            return [];
        }

        return [
            'provider' => $media->has('providerName') ? strtolower((string) $media->get('providerName')) : '',
            'oembed'   => $this->parseUrlAndAddAutoplay($oembed),
        ];
    }

    public function path(object $entity): string
    {
        $slug = $this->slugService->forEntity($entity);

        return $this->router->generate(
            'front',
            ['slug' => $slug]
        );
    }

    public function tarteaucitron(): string
    {
        $config = $this->configurationService->getConfiguration();
        if (in_array(trim((string) $config->getTacServices()), ['', '0'], true)) {
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

    /**
     * @param mixed[] $data
     */
    public function title(array $data): string
    {
        $request   = $this->requestStack->getCurrentRequest();
        $config    = $this->configurationService->getConfiguration();
        $siteTitle = $config->getName();
        $format    = $config->getTitleFormat();
        if ($this->siteService->isHome($data)) {
            return (string) $siteTitle;
        }

        $contentTitle = $this->siteService->getTitleMeta($data['entity']);
        $page         = $request->attributes->getInt('page', 1);
        if (1 != $page) {
            $contentTitle .= ' - Page ' . $page;
        }

        return str_replace(['%content_title%', '%site_name%'], [$contentTitle, $siteTitle], $format);
    }

    protected function getOEmbedUrl(string $html): ?string
    {
        $domDocument = new DOMDocument();
        $domDocument->loadHTML($html);

        $domNodeList = $domDocument->getElementsByTagName('iframe');
        if (0 === count($domNodeList)) {
            return null;
        }

        $iframe = $domNodeList->item(0);

        return $iframe->getAttribute('src');
    }

    protected function parseUrlAndAddAutoplay(string $url): string
    {
        $parse = parse_url($url);
        parse_str('' !== $parse['query'] && '0' !== $parse['query'] ? $parse['query'] : '', $args);
        $args['autoplay'] = 1;

        $newArgs        = http_build_query($args);
        $parse['query'] = $newArgs;

        return sprintf('%s://%s%s?%s', $parse['scheme'], $parse['host'], $parse['path'], $parse['query']);
    }
}
