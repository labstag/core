<?php

namespace Labstag\Service;

use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatableMessage;

class StoryService
{
    public function __construct(
        private RequestStack $requestStack
    )
    {
    }

    public function setPdf(Story $story): bool
    {
        $phpWord = new PhpWord();
        $data    = [
            'title'    => $story->getTitle(),
            'chapters' => [],
        ];
        $chapters = $story->getChapters();
        foreach ($chapters as $row) {
            if (!$row->isEnable()) {
                continue;
            }

            $this->setChapter($phpWord, $row);
            $chapter = [
                'title' => $row->getTitle(),
            ];

            $data['chapters'][] = $chapter;
        }

        if (0 != count($data['chapters'])) {
            $tempPath = $this->getFilename($story->getSlug(), 'odt');
            $writer   = IOFactory::createWriter($phpWord, 'ODText');
            $writer->save($tempPath);

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename((string) $tempPath),
                mimeType: mime_content_type($tempPath),
                test: true
            );

            $story->setPdfFile($uploadedFile);
            $this->getFlashBag()->add('success', new TranslatableMessage('Story file generated for %title%', ['%title%' => $story->getTitle()]));

            return true;
        }

        return false;
    }

    private function getFilename(?string $filename, string $extension = 'xlsx')
    {
        $originalExtension = pathinfo((string) $filename, PATHINFO_EXTENSION);

        return $this->getTemporaryFolder().'/'.str_replace('.'.$originalExtension, '.'.$extension, basename((string) $filename));
    }

    private function getFlashBag()
    {
        $session = $this->getSession();

        return $session->getFlashBag();
    }

    private function getSession(): \Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        return $this->requestStack->getSession();
    }

    private function getTemporaryFolder(): string
    {
        $tempFolder = sys_get_temp_dir();
        if (!is_dir($tempFolder) && (!mkdir($tempFolder) && !is_dir($tempFolder))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
        }

        return $tempFolder;
    }

    private function setChapter(PhpWord $phpWord, Chapter $chapter): void
    {
        $section    = $phpWord->addSection();
        $paragraphs = $chapter->getParagraphs();
        $section->addTitle($chapter->getTitle());
        foreach ($paragraphs as $paragraph) {
            $section->addText($paragraph->getContent());
        }
    }
}
