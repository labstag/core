<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\BlockExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Override;

class BlockExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('block_name', [BlockExtensionRuntime::class, 'getName']),
            new TwigFunction('block_id', [BlockExtensionRuntime::class, 'getId']),
            new TwigFunction('block_class', [BlockExtensionRuntime::class, 'getClass']),
            new TwigFunction(
                'block_show',
                [
                    BlockExtensionRuntime::class,
                    'getShow',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
