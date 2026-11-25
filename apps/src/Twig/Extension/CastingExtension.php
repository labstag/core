<?php

declare(strict_types=1);

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\CastingExtensionRuntime;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CastingExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('casting_cast', [CastingExtensionRuntime::class, 'cast']),
            new TwigFunction('casting_crew', [CastingExtensionRuntime::class, 'crew']),

            new TwigFunction('casting_acting', [CastingExtensionRuntime::class, 'acting']),
            new TwigFunction('casting_series', [CastingExtensionRuntime::class, 'series']),
            new TwigFunction('casting_episodes', [CastingExtensionRuntime::class, 'episodes']),
            new TwigFunction('casting_movies', [CastingExtensionRuntime::class, 'movies']),
        ];
    }
}
