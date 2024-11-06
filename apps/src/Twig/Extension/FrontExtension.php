<?php

namespace Labstag\Twig\Extension;

use Override;
use Labstag\Twig\Runtime\FrontExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FrontExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_path', [FrontExtensionRuntime::class, 'path']),
            new TwigFunction('site_title', [FrontExtensionRuntime::class, 'title']),
            new TwigFunction('site_metatags', [FrontExtensionRuntime::class, 'metatags'], ['is_safe' => ['html']]),
        ];
    }
}
