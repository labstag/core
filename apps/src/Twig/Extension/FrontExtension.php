<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\FrontExtensionRuntime;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FrontExtension extends AbstractExtension
{
    #[Override]
    public function getFilters()
    {
        return [new TwigFilter('enable', [FrontExtensionRuntime::class, 'enable'])];
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('oembed', [FrontExtensionRuntime::class, 'oembed']),
            new TwigFunction('site_path', [FrontExtensionRuntime::class, 'path']),
            new TwigFunction('site_title', [FrontExtensionRuntime::class, 'title']),
            new TwigFunction('site_asset', [FrontExtensionRuntime::class, 'asset']),
            new TwigFunction(
                'site_tarteaucitron',
                [
                    FrontExtensionRuntime::class,
                    'tarteaucitron',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'site_metatags',
                [
                    FrontExtensionRuntime::class,
                    'metatags',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'site_content',
                [
                    FrontExtensionRuntime::class,
                    'content',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
