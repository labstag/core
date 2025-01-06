<?php

namespace Labstag\Lib;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Knp\Component\Pager\PaginatorInterface;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Service\FileService;
use Labstag\Service\FormService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SitemapService;
use Labstag\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Vich\UploaderBundle\Form\Type\VichImageType;

abstract class ParagraphLib extends AbstractController
{

    public $template;

    protected $data = [];

    protected $footer = [];

    protected $header = [];

    protected $show = [];

    protected array $templates = [];

    public function __construct(
        protected FormService $formService,
        protected SitemapService $sitemapService,
        protected RequestStack $requestStack,
        protected PaginatorInterface $paginator,
        protected FileService $fileService,
        protected SiteService $siteService,
        protected ManagerRegistry $managerRegistry,
        protected ParagraphService $paragraphService,
        protected Environment $twigEnvironment
    )
    {
    }

    public function addFieldImageUpload(string $type, $pageName)
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $textField = TextField::new($type.'File');
            $textField->setFormType(VichImageType::class);

            return $textField;
        }

        $basePath   = $this->fileService->getBasePath(Paragraph::class, $type.'File');
        $imageField = ImageField::new($type);
        $imageField->setBasePath($basePath);

        return $imageField;
    }

    public function addFieldIntegerNbr()
    {
        $integerField = IntegerField::new('nbr');
        $integerField->setFormTypeOption('attr', ['min' => 1]);

        return $integerField;
    }

    public function content(string $view, Paragraph $paragraph): ?Response
    {
        if (!$this->isShow($paragraph)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($paragraph)
        );
    }

    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($paragraph, $data, $disable);
    }

    public function getData(Paragraph $paragraph)
    {
        $paragraphId = $paragraph->getId();

        return $this->data[$paragraphId] ?? [];
    }

    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);

        return [];
    }

    public function getFooter(Paragraph $paragraph)
    {
        $paragraphId = $paragraph->getId();

        return $this->footer[$paragraphId] ?? null;
    }

    public function getHeader(Paragraph $paragraph)
    {
        $paragraphId = $paragraph->getId();

        return $this->header[$paragraphId] ?? null;
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

    public function isShow(Paragraph $paragraph)
    {
        $paragraphId = $paragraph->getId();

        return $this->show[$paragraphId] ?? true;
    }

    public function templates(string $type): array
    {
        return $this->getTemplateContent($type, $this->getType());
    }

    public function useIn(): array
    {
        return [];
    }

    protected function getPaginator($query, ?int $limit)
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );
    }

    protected function getRepository(string $entity)
    {
        return $this->managerRegistry->getRepository($entity);
    }

    protected function getTemplateContent(string $folder, string $type)
    {
        if (isset($this->template[$type])) {
            return $this->templates[$type];
        }

        $htmltwig = '.html.twig';
        $files    = [
            'paragraphs/'.$folder.'/'.$type.$htmltwig,
            'paragraphs/'.$folder.'/default'.$htmltwig,
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

    protected function setData(Paragraph $paragraph, array $data)
    {
        $this->setShow($paragraph, true);

        $data['configuration'] = $this->siteService->getConfiguration();

        $this->data[$paragraph->getId()] = $data;
    }

    protected function setFooter(Paragraph $paragraph, $data)
    {
        $paragraphId = $paragraph->getId();

        $this->footer[$paragraphId] = $data;
    }

    protected function setHeader(Paragraph $paragraph, $data)
    {
        $paragraphId = $paragraph->getId();

        $this->header[$paragraphId] = $data;
    }

    protected function setShow(Paragraph $paragraph, $show)
    {
        if (isset($this->show[$paragraph->getId()])) {
            return;
        }

        $this->show[$paragraph->getId()] = $show;
    }

    protected function useInAll(): array
    {
        return [
            Block::class,
            Chapter::class,
            Edito::class,
            Story::class,
            Memo::class,
            Page::class,
            Post::class,
        ];
    }
}
