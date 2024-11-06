<?php

namespace Labstag\Lib;

use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

abstract class ParagraphLib extends AbstractController
{

    public $template;
    protected array $templates = [];

    public function __construct(
        protected Environment $twigEnvironment
    )
    {
    }

    public function content(string $view, Paragraph $paragraph, array $data)
    {
        unset($view, $paragraph, $data);
    }

    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    public function getName(): string
    {
        return '';
    }

    public function getType(): string
    {
        return '';
    }

    public function isEnable(): bool
    {
        return true;
    }

    public function templates(): array
    {
        $data = $this->getTemplateData($this->getType());
        if ('dev' == $this->getParameter('kernel.debug')) {
            return $data;
        }

        return [];
    }

    public function useIn(): array
    {
        return [];
    }

    protected function getTemplateData(string $type)
    {
        if (isset($this->template[$type])) {
            return $this->templates[$type];
        }

        $htmltwig = '.html.twig';
        $files    = [
            'paragraphs/'.$type.$htmltwig,
            'paragraphs/default'.$htmltwig,
        ];

        $view   = end($files);
        $loader = $this->twigEnvironment->getLoader();
        foreach ($files as $file) {
            if (!$loader->exists($file)) {
                continue;
            }

            $view = $file;

            break;
        }

        $this->templates[$type] = [
            'hook'  => 'paragraph',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates[$type];
    }

    protected function useInAll(): array
    {
        return [
            Block::class,
            Chapter::class,
            Edito::class,
            History::class,
            Memo::class,
            Page::class,
            Post::class,
        ];
    }
}
