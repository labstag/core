<?php

namespace Labstag\Lib;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Service\ParagraphService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Vich\UploaderBundle\Form\Type\VichImageType;

abstract class ParagraphLib extends AbstractController
{

    public $template;

    protected array $templates = [];

    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected ParagraphService $paragraphService,
        protected Environment $twigEnvironment
    )
    {
    }

    public function addFieldImageUpload(string $type)
    {
        $textField = TextField::new($type.'File');
        $textField->setFormType(VichImageType::class);

        return $textField;
    }

    public function content(string $view, Paragraph $paragraph, ?array $data = null)
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

    protected function getRepository(string $entity)
    {
        return $this->managerRegistry->getRepository($entity);
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
