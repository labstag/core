<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Essence\Essence;
use Essence\Media;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatableMessage;

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
        if (is_null($url) || '' === $url || '0' === $url) {
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
                'title'     => $media->has('title') ? $media->get('title') : '',
                'provider'  => $media->has('providerName') ? strtolower((string) $media->get('providerName')) : '',
                'oembed'    => $this->parseUrlAndAddAutoplay($oembed),
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function update(Paragraph $paragraph): void
    {
        if (!is_null($paragraph->getImg())) {
            return;
        }

        $url = $paragraph->getUrl();
        if (is_null($url) || '' === $url || '0' === $url) {
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

        if (!$media->has('thumbnailUrl')) {
            return;
        }

        $thumbnailUrl = $media->get('thumbnailUrl');
        $tempPath     = tempnam(sys_get_temp_dir(), 'poster_');

        // Télécharger l'image et l'écrire dans le fichier temporaire
        file_put_contents($tempPath, file_get_contents($thumbnailUrl));

        $uploadedFile = new UploadedFile(
            path: $tempPath,
            originalName: basename($tempPath),
            mimeType: mime_content_type($tempPath),
            test: true
        );

        $paragraph->setImgFile($uploadedFile);
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph);
        yield $this->addFieldImageUpload('img', $pageName);
        yield UrlField::new('url', new TranslatableMessage('Url'));
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
