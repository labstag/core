<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\AdminExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_url', [AdminExtensionRuntime::class, 'url']),
            new TwigFunction('admin_recommandations', [AdminExtensionRuntime::class, 'recommandations']),
            new TwigFunction('admin_name', [AdminExtensionRuntime::class, 'name']),
        ];
    }
}
