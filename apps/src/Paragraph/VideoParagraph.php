<?php

namespace Labstag\Paragraph;

use DOMDocument;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Essence\Essence;
use Essence\Media;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class VideoParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $url = $paragraph->getUrl();
        if (is_null($url) || $url === '' || $url === '0') {
            $this->setShow($paragraph, false);

            return;
        }

        $essence = new Essence();

        // Load any url:
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

        $html   = $media->has('html') ? $media->get('html') : '';
        $oembed = $this->getOEmbedUrl($html);
        if (is_null($oembed)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'image'     => $media->has('thumbnailUrl') ? $media->get('thumbnailUrl') : '',
                'oembed'    => $this->parseUrlAndAddAutoplay($oembed),
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): iterable
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
    
    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
