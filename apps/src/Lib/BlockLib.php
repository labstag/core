<?php

namespace Labstag\Lib;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Block;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

abstract class BlockLib extends AbstractController
{

    public $template;

    protected $data = [];

    protected $footer = [];

    protected $header = [];

    protected $show = [];

    protected array $templates = [];

    public function __construct(
        protected AdminUrlGenerator $adminUrlGenerator,
        protected ParagraphService $paragraphService,
        protected SiteService $siteService,
        protected RequestStack $requestStack,
        protected Environment $twigEnvironment
    )
    {
    }

    public function content(string $view, Block $block)
    {
        unset($view, $block);
    }

    public function generate(Block $block, array $data)
    {
        unset($block, $data);
    }

    public function getData(Block $block)
    {
        $blockId = $block->getId();

        return $this->data[$blockId] ?? null;
    }

    public function getFields(Block $block, $pageName): iterable
    {
        unset($block, $pageName);

        return [];
    }

    public function getFooter(Block $block)
    {
        $blockId = $block->getId();

        return $this->footer[$blockId] ?? null;
    }

    public function getHeader(Block $block)
    {
        $blockId = $block->getId();

        return $this->header[$blockId] ?? null;
    }

    public function getName(): string
    {
        return '';
    }

    public function getType(): string
    {
        return '';
    }

    public function isShow(Block $block)
    {
        $blockId = $block->getId();

        return $this->show[$blockId] ?? true;
    }

    public function templates(string $type): array
    {
        return $this->getTemplateContent($type, $this->getType());
    }

    public function useIn(): array
    {
        return [];
    }

    protected function getTemplateContent(string $folder, string $type)
    {
        if (isset($this->template[$type])) {
            return $this->templates[$type];
        }

        $htmltwig = '.html.twig';
        $files    = [
            'blocks/'.$folder.'/'.$type.$htmltwig,
            'blocks/'.$folder.'/default'.$htmltwig,
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

    protected function setData(Block $block, array $data)
    {
        $this->setShow($block, true);

        $data['configuration'] = $this->siteService->getConfiguration();

        $this->data[$block->getId()] = $data;
    }

    protected function setFooter(Block $block, $data)
    {
        $blockId = $block->getId();

        $this->footer[$blockId] = $data;
    }

    protected function setHeader(Block $block, $data)
    {
        $blockId = $block->getId();

        $this->header[$blockId] = $data;
    }

    protected function setShow(Block $block, $show)
    {
        $this->show[$block->getId()] = $show;
    }
}
