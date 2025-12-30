<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\CastingExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CastingExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('casting_cast', [CastingExtensionRuntime::class, 'cast']),
            new TwigFunction('casting_acting', [CastingExtensionRuntime::class, 'acting']),
            new TwigFunction('casting_series', [CastingExtensionRuntime::class, 'series']),
            new TwigFunction('casting_movies', [CastingExtensionRuntime::class, 'movies']),
            new TwigFunction('casting_directing', [CastingExtensionRuntime::class, 'directing']),
            new TwigFunction('casting_writing', [CastingExtensionRuntime::class, 'writing']),
            new TwigFunction('casting_production', [CastingExtensionRuntime::class, 'production']),
            new TwigFunction('casting_editing', [CastingExtensionRuntime::class, 'editing']),
        ];
    }
}
