<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class VideoParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph)
    {
        if (!$this->isShow($paragraph)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($paragraph)
        );
    }

    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        $baseUrlVideo = $this->fileService->getBasePath(Paragraph::class, 'imgFile');
        $this->setData(
            $paragraph,
            [
                'baseUrlVideo' => $baseUrlVideo,
                'paragraph'    => $paragraph,
                'data'         => $data,
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
}
