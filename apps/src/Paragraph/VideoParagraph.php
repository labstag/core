<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Essence\Media;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\VideoParagraph as EntityVideoParagraph;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class VideoParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        $media = $this->fileService->getMediaByUrl($paragraph->getUrl());
        unset($disable);
        if (!$media instanceof Media) {
            $this->setShow($paragraph, false);

            return;
        }

        $json = $media->jsonSerialize();
        $oembed = $this->getOEmbedUrl($json['html'] ?? '');
        if (is_null($oembed)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'title'     => $json['title'] ?? '',
                'provider'  => $json['providerName'] ?? '',
                'oembed'    => $this->parseUrlAndAddAutoplay($oembed),
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityVideoParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): Generator
    {
        yield $this->addFieldImageUpload('img', $pageName, $paragraph);
        yield UrlField::new('url', new TranslatableMessage('Url'));
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Video');
    }

    #[Override]
    public function getType(): string
    {
        return 'video';
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
        if (!$paragraph instanceof EntityVideoParagraph) {
            return;
        }

        if (!is_null($paragraph->getImg()) && is_null($paragraph->getImgFile())) {
            return;
        }

        $media = $this->fileService->getMediaByUrl($paragraph->getUrl());
        if (is_null($media)) {
            return;
        }
        
        $json = $media->jsonSerialize();
        $this->fileService->setUploadedFile($json['thumbnail_url'] ?? '', $paragraph, 'imgFile');
    }
}
