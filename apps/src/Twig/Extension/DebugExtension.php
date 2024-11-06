<?php

namespace Labstag\Twig\Extension;

use Override;
use Labstag\Twig\Runtime\DebugExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DebugExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('debug_begin', [DebugExtensionRuntime::class, 'begin'], ['is_safe' => ['html']]),
            new TwigFunction('debug_end', [DebugExtensionRuntime::class, 'end'], ['is_safe' => ['html']]),
        ];
    }
}
