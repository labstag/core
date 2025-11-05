<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\ParagraphExtensionRuntime;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ParagraphExtension extends AbstractExtension
{
    /**
     * @return mixed[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('paragraph_type', [ParagraphExtensionRuntime::class, 'type']),
            new TwigFunction('paragraph_name', [ParagraphExtensionRuntime::class, 'name']),
            new TwigFunction('paragraph_contextmenu', [ParagraphExtensionRuntime::class, 'getContextMenu']),
            new TwigFunction('paragraph_fond', [ParagraphExtensionRuntime::class, 'getFond']),
            new TwigFunction(
                'paragraph_show',
                [
                    ParagraphExtensionRuntime::class,
                    'getShow',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
