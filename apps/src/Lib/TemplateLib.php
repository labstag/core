<?php

namespace Labstag\Lib;

use Labstag\Repository\TemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

abstract class TemplateLib extends AbstractController
{

    public $template;

    protected array $data = [];

    protected array $templates = [];

    public function __construct(
        protected Environment $twigEnvironment,
        protected TemplateRepository $templateRepository
    )
    {
    }

    public function getEntity()
    {
        return $this->templateRepository->findOneBy(
            [
                'code' => $this->getType(),
            ]
        );
    }

    public function getHelp()
    {
        if ('' === $this->getType()) {
            return null;
        }

        $render = $this->getTemplate('help.html');

        return $render->getContent();
    }

    public function getHtml()
    {
        $entity = $this->getEntity();

        return $entity->getHtml();
    }

    public function getName(): string
    {
        return '';
    }

    public function getSubject()
    {
        $entity = $this->getEntity();

        return $entity->getTitle();
    }

    public function getText()
    {
        $entity = $this->getEntity();

        return $entity->getText();
    }

    public function getType(): string
    {
        return '';
    }

    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    public function setHtml()
    {
        if ('' === $this->getType()) {
            return null;
        }

        $render = $this->getTemplate('html');

        return $render->getContent();
    }

    public function setText()
    {
        if ('' === $this->getType()) {
            return null;
        }

        $render = $this->getTemplate('txt');

        return $render->getContent();
    }

    protected function getTemplateContent(string $folder, string $type)
    {
        if (isset($this->template[$type])) {
            return $this->templates[$type];
        }

        $twig  = '.'.$type.'.twig';
        $files = [
            'templates/'.$folder.$twig,
            'templates/default'.$twig,
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

    private function getTemplate($type)
    {
        $templates = $this->templates($type);

        return $this->render(
            $templates['view'],
            [
                'type' => $this->getType(),
                'code' => $type,
            ]
        );
    }

    private function templates(string $type): array
    {
        return $this->getTemplateContent($this->getType(), $type);
    }
}
