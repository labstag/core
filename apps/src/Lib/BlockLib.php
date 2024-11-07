<?php

namespace Labstag\Lib;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Block;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

abstract class BlockLib extends AbstractController
{

    public $template;

    protected array $templates = [];

    public function __construct(
        protected AdminUrlGenerator $adminUrlGenerator,
        protected ParagraphService $paragraphService,
        protected SiteService $siteService,
        protected Environment $twigEnvironment
    )
    {
    }

    public function content(string $view, Block $block, array $data)
    {
        unset($view, $block, $data);
    }

    public function getFields(Block $block, $pageName): iterable
    {
        unset($block, $pageName);

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
            'blocks/'.$type.$htmltwig,
            'blocks/default'.$htmltwig,
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
            'hook'  => 'block',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates[$type];
    }
}
