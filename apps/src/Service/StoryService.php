<?php

namespace Labstag\Service;

use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoryService
{

    private array $stories = [];

    public function __construct(
        private KernelInterface $kernel,
        protected TranslatorInterface $translator
    )
    {
    }

    private function getChapters(Story $story): array
    {
        $chapters = [];
        $data     = $story->getChapters();
        foreach ($data as $row) {
            if (!$row->isEnable()) {
                continue;
            }

            $chapters[] = $row;
        }

        return $chapters;
    }

    public function setPdf(Story $story): bool
    {
        $tempPath = $this->getTemporaryFolder() . '/' . $story->getSlug() . '.docx';

        $state = $this->setDocX($tempPath, $story);
        if (!$state) {
            return false;
        }

        $uploadedFile = new UploadedFile(
            path: $tempPath,
            originalName: basename($tempPath),
            mimeType: mime_content_type($tempPath),
            test: true
        );

        $story->setPdfFile($uploadedFile);
        $this->stories[] = $story->getTitle();

        return true;
    }

    private function setDocX(string $docxFile, Story $story): bool
    {
        $phpWord = new PhpWord();
        $section  = $phpWord->addSection();
        $chapters = $this->getChapters($story);
        if (0 == count($chapters)) {
            return false;
        }

        $this->addCoverPage($section, $story);
        $this->addSummary($section);

        foreach ($chapters as $chapter) {
            $this->setChapter($section, $chapter);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($docxFile);

        return true;
    }

    private function addSummary(Section $section): void
    {
        $section->addText(
            'Table des matières',
            [
                'size' => 18,
                'bold' => true,
            ]
        );
        $toc = $section->addTOC(
            [
                'spaceAfter' => 60,
                'size'       => 12,
            ]
        );
        $toc->setMinDepth(0);
        $section->addPageBreak();
    }

    private function addCoverPage(Section $section, Story $story): void
    {
        // Ajout de la page de garde
        $section->addTextBreak(10);
        $section->addText(
            $story->getTitle(),
            [
                'size' => 24,
                'bold' => true,
            ],
            ['align' => 'center']
        );
        $section->addTextBreak(2);
        $section->addText(
            $story->getRefuser()->getUsername(),
            ['size' => 16],
            ['align' => 'center']
        );

        $section->addPageBreak();
    }

    public function generateFlashBag(): string
    {
        return $this->translator->trans(
            'Stories file (%count%) generated for %stories%',
            [
                '%stories%' => implode(', ', $this->stories),
                '%count%'   => count($this->stories),
            ]
        );
    }

    public function getUpdates(): array
    {
        return $this->stories;
    }

    private function getTemporaryFolder(): string
    {
        $tempFolder = sys_get_temp_dir();
        if (!is_dir($tempFolder) && (!mkdir($tempFolder) && !is_dir($tempFolder))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
        }

        return $tempFolder;
    }

    private function setChapter(Section $section, Chapter $chapter): void
    {
        $paragraphs = $chapter->getParagraphs();
        $section->addTitle($chapter->getTitle(), 1);
        $section->addTextBreak(2);
        foreach ($paragraphs as $paragraph) {
            $section->addText(
                $paragraph->getContent(),
                [
                    'size' => 12,
                    'bold' => false,
                ],
                ['spaceAfter' => 240]
            );
        }

        $section->addPageBreak();
    }
}
