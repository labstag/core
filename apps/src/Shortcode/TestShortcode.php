<?php

namespace Labstag\Shortcode;

class TestShortcode extends ShortcodeAbstract
{
    public function content(array $matches): string
    {
        return '';
    }

    public function generateShortcode1(string $id): string
    {
        return sprintf('[%s:%s]', 'storyurl', $id);
    }

    public function getPattern(): string
    {
        return '/\[(\w+)(.*?)\]/';
    }
}
