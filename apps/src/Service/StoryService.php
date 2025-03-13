<?php

namespace Labstag\Service;

use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Mpdf\Mpdf;
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
        $tempPath = $this->getTemporaryFolder() . '/' . $story->getSlug() . '.pdf';

        $mpdf = new Mpdf(['tempDir' => $this->getTemporaryFolder() . '/tmp']);
        $mpdf->SetAuthor($story->getRefuser()->getUsername());
        $mpdf->SetTitle($story->getTitle());
        $this->addCoverPage($mpdf, $story);
        $chapters = $this->getChapters($story);
        if (0 == count($chapters)) {
            return false;
        }

        $mpdf->TOCpagebreakByArray(
            [
                'toc-preHTML' => '<h1>Table des matières</h1>',
                'links' => true
            ]
        );

        foreach ($chapters as $chapter) {
            $this->setChapter($mpdf, $chapter);
        }


        $mpdf->Output($tempPath, 'F');
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

    private function addCoverPage($mpdf, Story $story): void
    {
        $mpdf->WriteHTML('
            <div style="text-align:center;">
                <h1>'.$story->getTitle().'</h1>
                <h3>Auteur : '.$story->getRefuser()->getUsername().'</h3>
            </div>
        ');

        $mpdf->AddPage();
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

    private function setChapter($mpdf, Chapter $chapter): void
    {
        $paragraphs = $chapter->getParagraphs();
        $mpdf->TOC_Entry($chapter->getTitle(), 0);
        $i = 0;
        foreach ($paragraphs as $paragraph) {
            if ($paragraph->getType() == 'text') {
                if ($i == 0) {
                    $mpdf->WriteHTML('<h2>'.$chapter->getTitle().'</h2>');
                }
                $mpdf->WriteHTML($paragraph->getContent());
                    $mpdf->AddPage();
            }
            $i++;
        }

        // $mpdf->WriteHTML('<pagebreak />');
    }
}
