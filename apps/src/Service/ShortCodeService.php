<?php

namespace Labstag\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ShortCodeService
{
    public function __construct(
        #[AutowireIterator('labstag.shortcodes')]
        protected iterable $shortcodes,
    )
    {
    }

    /**
     * Process URL and return entity or original URL.
     */
    public function getContent(string $url): ?string
    {
        foreach ($this->shortcodes as $shortcode) {
            $pattern = $shortcode->getPattern();
            if (preg_match($pattern, $url, $matches)) {
                return $shortcode->content($matches);
            }
        }

        return $url;
    }
}
