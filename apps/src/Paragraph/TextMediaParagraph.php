<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Essence\Essence;
use Essence\Media;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Entity\TextMediaParagraph as EntityTextMediaParagraph;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TextMediaParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        $media = $this->getMedia($paragraph);

        unset($disable);
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

    public function getClass(): string
    {
        return EntityTextMediaParagraph::class;
    }

    #[Override]
    public function getClasses(Paragraph $paragraph): array
    {
        $tab = parent::getClasses($paragraph);
        if ($paragraph instanceof EntityTextMediaParagraph && $paragraph->isLeftposition()) {
            $tab[] = 'text-media-left';
        }

        return $tab;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): \Generator
    {
        yield $this->addFieldImageUpload('img', $pageName, $paragraph);

        yield BooleanField::new('leftposition', new TranslatableMessage('Media on the left'));
        yield UrlField::new('url', new TranslatableMessage('Url'));
        $wysiwygField = WysiwygField::new('content', new TranslatableMessage('Text'));

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Text media');
    }

    #[Override]
    public function getType(): string
    {
        return 'text-media';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $inArray = in_array(
            $object::class,
            [
                Block::class,
                Chapter::class,
                Edito::class,
                Story::class,
                Memo::class,
                Page::class,
                Post::class,
            ]
        );

        return $inArray || $object instanceof Block;
    }

    #[Override]
    public function update(Paragraph $paragraph): void
    {
        if (!$paragraph instanceof EntityTextMediaParagraph) {
            return;
        }

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
        $this->fileService->setUploadedFile($tempPath, $paragraph, 'imgFile');
    }

    protected function getMedia(Paragraph $paragraph): ?Media
    {
        if (!$paragraph instanceof EntityTextMediaParagraph) {
            return null;
        }

        $url = $paragraph->getUrl();
        if (is_null($url) || '' === $url || '0' === $url) {
            return null;
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
            return null;
        }

        return $media;
    }
}
