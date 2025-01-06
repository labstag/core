<?php

namespace Labstag\Paragraph;

use DOMDocument;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Essence\Essence;
use Essence\Media;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class VideoParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $url = $paragraph->getUrl();
        if (null === $url || '' === $url || '0' === $url) {
            $this->setShow($paragraph, false);

            return;
        }

        $essence = new Essence();

        //Load any url:
        $media = $essence->extract(
            $url,
            [
                'maxwidth'  => 800,
                'maxheight' => 600,
            ]
        );
        if (!$media instanceof Media) {
            $this->setShow($paragraph, false);

            return;
        }

        $html   = $media->html;
        $oembed = $this->getOEmbedUrl($html);
        if (is_null($oembed)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'image'     => $media->thumbnailUrl,
                'oembed'    => $this->parseUrlAndAddAutoplay($oembed),
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph);
        yield $this->addFieldImageUpload('img', $pageName);
        yield UrlField::new('url');
    }

    #[Override]
    public function getName(): string
    {
        return 'Video';
    }

    #[Override]
    public function getType(): string
    {
        return 'video';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }

    private function getOEmbedUrl($html)
    {
        $domDocument = new DOMDocument();
        $domDocument->loadHTML($html);

        $domNodeList = $domDocument->getElementsByTagName('iframe');
        if (0 == count($domNodeList)) {
            return null;
        }

        $iframe = $domNodeList->item(0);

        return $iframe->getAttribute('src');
    }

    private function parseUrlAndAddAutoplay($url): string
    {
        $parse = parse_url((string) $url);
        parse_str('' !== $parse['query'] && '0' !== $parse['query'] ? $parse['query'] : '', $args);
        $args['autoplay'] = 1;

        $newArgs        = http_build_query($args);
        $parse['query'] = $newArgs;

        return sprintf(
            '%s://%s%s?%s',
            $parse['scheme'],
            $parse['host'],
            $parse['path'],
            $parse['query']
        );
    }
}
