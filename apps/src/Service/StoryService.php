<?php

namespace Labstag\Service;

use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use PhpOffice\PhpWord\Settings;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatableMessage;

class StoryService
{
    private array $stories = [];


    public function __construct(
        private RequestStack $requestStack,
        private KernelInterface $kernel
    )
    {
    }

    private function getChapters(Story $story): array
    {
        $chapters = [];
        $data = $story->getChapters();
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
        $phpWord = new PhpWord();
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath($this->kernel->getProjectDir() . '/vendor/dompdf/dompdf');
        $section = $phpWord->addSection();
        $chapters = $this->getChapters($story);
        if (count($chapters) == 0) {
            return false;
        }

        $this->addCoverPage($section, $story, $chapters);
        $this->addSummary($section);

        foreach ($chapters as $chapter) {
            $this->setChapter($section, $chapter);
        }

        $tempPath = $this->getTemporaryFolder() . '/' . $story->getSlug()  . '.pdf';
        $writer   = IOFactory::createWriter($phpWord, 'PDF');
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            path: $tempPath,
            originalName: basename((string) $tempPath),
            mimeType: mime_content_type($tempPath),
            test: true
        );

        $story->setPdfFile($uploadedFile);
        $this->stories[] = $story->getTitle();

        return true;
    }

    private function addSummary($section)
    {
        $section->addText(
            "Table des matières",
            [
                'size' => 18, 'bold' => true
            ]
        );
        $toc = $section->addTOC(
            ['spaceAfter' => 60, 'size' => 12]
        );
        $toc->setMinDepth(0);
        $section->addPageBreak();
        $section->addPageBreak();
    }

    private function addCoverPage($section, Story $story, array $chapters): void
    {        
        // Ajout de la page de garde
        $section->addTextBreak(10);
        $section->addText(
            $story->getTitle(),
            ['size' => 24, 'bold' => true],
            ['align' => 'center']
        );
        $section->addTextBreak(2);
        $section->addText(
            $story->getRefuser()->getUsername(),
            ['size' => 16],
            ['align' => 'center']
        );
        
        $section->addPageBreak();
        $section->addPageBreak();
    }

    public function generateFlashBag()
    {
        $this->getFlashBag()->add(
            'success',
            new TranslatableMessage(
                'Story file generated for "%title%"',
                [
                    '%title%' => implode('"," ', $this->stories),
                ]
            )
        );
    }

    private function getFlashBag()
    {
        $session = $this->getSession();

        if (!method_exists($session, 'getFlashBag')) {
            throw new RuntimeException('FlashBag not found');
        }

        return $session->getFlashBag();
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
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

    private function setChapter($section, Chapter $chapter): void
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
                [
                    'spaceAfter' => 240
                ]
            );
        }

        $section->addPageBreak();
        $section->addPageBreak();
    }
}
