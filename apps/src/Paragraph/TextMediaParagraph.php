<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Essence\Media;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
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
        $media = $this->getMediaByUrl($paragraph->getUrl());

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
    public function getFields(Paragraph $paragraph, string $pageName): Generator
    {
        yield $this->addFieldImageUpload('img', $pageName, $paragraph);

        yield BooleanField::new('leftposition', new TranslatableMessage('Media on the left'));
        yield UrlField::new('url', new TranslatableMessage('Url'));
        $wysiwygField = WysiwygField::new('content', new TranslatableMessage('Text'));

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Text media');
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

        $inArray = in_array($object::class, [Block::class, Edito::class, Memo::class, Page::class, Post::class]);

        return $inArray || $object instanceof Block;
    }

    #[Override]
    public function update(Paragraph $paragraph): void
    {
        if (!$paragraph instanceof EntityTextMediaParagraph) {
            return;
        }

        if (!is_null($paragraph->getImg()) && is_null($paragraph->getImgFile())) {
            return;
        }

        $media = $this->getMediaByUrl($paragraph->getUrl());
        if (is_null($media)) {
            return;
        }

        $thumbnailUrl = $media->get('thumbnailUrl');
        $this->fileService->setUploadedFile($thumbnailUrl, $paragraph, 'imgFile');
    }
}
