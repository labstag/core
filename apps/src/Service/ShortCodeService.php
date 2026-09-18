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

    public function changeContent(string $content): string
    {
        foreach ($this->shortcodes as $shortcode) {
            $pattern = $shortcode->getPattern();
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key => $id) {
                    $replace = $shortcode->content($id);
                    if (null !== $replace) {
                        $content = str_replace($matches[0][$key], $replace, $content);
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Process URL and return entity or original URL.
     */
    public function getContent(string $url): ?string
    {
        foreach ($this->shortcodes as $shortcode) {
            $pattern = $shortcode->getPattern();
            if (preg_match($pattern, $url, $matches)) {
                return $shortcode->content($matches[1]);
            }
        }

        return $url;
    }
}
