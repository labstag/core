<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\BlockExtensionRuntime;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    /**
     * @return mixed[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('block_contextmenu', [BlockExtensionRuntime::class, 'getContextMenu']),
            new TwigFunction('block_type', [BlockExtensionRuntime::class, 'type']),
            new TwigFunction('block_name', [BlockExtensionRuntime::class, 'name']),
            new TwigFunction(
                'block_show',
                [BlockExtensionRuntime::class, 'getShow'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
