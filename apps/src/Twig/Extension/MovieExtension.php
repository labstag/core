<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\MovieExtensionRuntime;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MovieExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('movie_oembed', [MovieExtensionRuntime::class, 'oembed']),
        ];
    }
}
