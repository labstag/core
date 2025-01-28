<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\MovieExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MovieExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('movie_oembed', [MovieExtensionRuntime::class, 'oembed']),
        ];
    }
}
