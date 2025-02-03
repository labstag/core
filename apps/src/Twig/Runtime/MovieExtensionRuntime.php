<?php

namespace Labstag\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class MovieExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function oembed(?string $url): string
    {
        if (is_null($url)) {
            return '';
        }

        preg_match('/(?P<videoId>vi\d+)/', $url, $matches);

        if (!isset($matches['videoId'])) {
            return '';
        }

        return 'https://www.imdb.com/videoembed/' . $matches['videoId'];
        // ...
    }
}
