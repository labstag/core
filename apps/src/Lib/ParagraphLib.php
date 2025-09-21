<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Labstag\Controller\Admin\ParagraphCrudController;
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
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Environment;
use Vich\UploaderBundle\Form\Type\VichImageType;

abstract class ParagraphLib extends AbstractController
{

    /**
     * @var mixed[]
     */
    protected array $data = [];

    /**
     * @var mixed[]
     */
    protected array $footer = [];

    /**
     * @var Response[]
     */
    protected array $header = [];

    /**
     * @var mixed[]
     */
    protected array $show = [];

    /**
     * @var mixed[]
     */
    protected array $templates = [];

    public function __construct(
        protected LoggerInterface $logger,
        protected Security $security,
        protected AdminUrlGenerator $adminUrlGenerator,
        protected FormService $formService,
        protected SitemapService $sitemapService,
        protected RequestStack $requestStack,
        protected PaginatorInterface $paginator,
        protected FileService $fileService,
        protected SiteService $siteService,
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function addFieldImageUpload(string $type, string $pageName): TextField|ImageField
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $textField = TextField::new($type . 'File', new TranslatableMessage('Image'));
            $textField->setFormType(VichImageType::class);

            return $textField;
        }

        $basePath   = $this->fileService->getBasePath(Paragraph::class, $type . 'File');
        $imageField = ImageField::new($type, new TranslatableMessage('Image'));
        $imageField->setBasePath($basePath);

        return $imageField;
    }

    public function addFieldIntegerNbr(): IntegerField
    {
        $integerField = IntegerField::new('nbr', new TranslatableMessage('Number'));
        $integerField->setFormTypeOption(
            'attr',
            ['min' => 1]
        );

        return $integerField;
    }

    public function content(string $view, Paragraph $paragraph): ?Response
    {
        if (!$this->isShow($paragraph)) {
            return null;
        }

        return $this->render($view, $this->getData($paragraph));
    }

    /**
     * @param mixed[] $data
     */
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($paragraph, $data, $disable);
    }

    public function getClasses(Paragraph $paragraph): array
    {
        return explode(' ', (string) $paragraph->getClasses());
    }

    /**
     * @return mixed[]
     */
    public function getData(Paragraph $paragraph): array
    {
        $paragraphId = $paragraph->getId();

        return $this->data[$paragraphId] ?? [];
    }

    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);

        return [];
    }

    public function getFooter(Paragraph $paragraph): mixed
    {
        $paragraphId = $paragraph->getId();

        return $this->footer[$paragraphId] ?? null;
    }

    public function getHeader(Paragraph $paragraph): mixed
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

    public function isShow(Paragraph $paragraph): bool
    {
        $paragraphId = $paragraph->getId();

        return $this->show[$paragraphId] ?? true;
    }

    /**
     * @return mixed[]
     */
    public function templates(Paragraph $paragraph, string $type): array
    {
        unset($paragraph);

        return $this->getTemplateContent($type, $this->getType());
    }

    public function update(Paragraph $paragraph): void
    {
        unset($paragraph);
    }

    /**
     * @return mixed[]
     */
    public function useIn(): array
    {
        return [];
    }

    protected function getOEmbedUrl(string $html): ?string
    {
        $domDocument = new DOMDocument();
        $domDocument->loadHTML($html);

        $domNodeList = $domDocument->getElementsByTagName('iframe');
        if (0 == count($domNodeList)) {
            return null;
        }

        $iframe = $domNodeList->item(0);

        return $iframe->getAttribute('src');
    }

    protected function getPaginator(mixed $query, ?int $limit): PaginationInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->paginator->paginate($query, $request->attributes->getInt('page', 1), $limit);
    }

    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }

    /**
     * @return mixed[]
     */
    protected function getTemplateContent(string $folder, string $type): array
    {
        if (isset($this->templates[$folder][$type])) {
            return $this->templates[$folder][$type];
        }

        $htmltwig = '.html.twig';
        $files    = [
            'paragraphs/' . $folder . '/' . $type . $htmltwig,
            'paragraphs/' . $folder . '/default' . $htmltwig,
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

        if ($view == end($files)) {
            $this->logger->error(
                'Template not found',
                [
                    'folder' => $folder,
                    'type'   => $type,
                ]
            );
        }

        $this->templates[$folder][$type] = [
            'hook'  => 'paragraph',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates[$folder][$type];
    }

    protected function parseUrlAndAddAutoplay(string $url): string
    {
        $parse = parse_url($url);
        parse_str('' !== $parse['query'] && '0' !== $parse['query'] ? $parse['query'] : '', $args);
        $args['autoplay'] = 1;

        $newArgs        = http_build_query($args);
        $parse['query'] = $newArgs;

        return sprintf('%s://%s%s?%s', $parse['scheme'], $parse['host'], $parse['path'], $parse['query']);
    }

    /**
     * @param mixed[] $data
     */
    protected function setData(Paragraph $paragraph, array $data): void
    {
        $this->setShow($paragraph, true);

        $data['url_admin']     = $this->setUrlAdmin($paragraph);
        $data['configuration'] = $this->siteService->getConfiguration();

        $this->data[$paragraph->getId()] = $data;
    }

    protected function setFooter(Paragraph $paragraph, mixed $data): void
    {
        $paragraphId = $paragraph->getId();

        $this->footer[$paragraphId] = $data;
    }

    protected function setHeader(Paragraph $paragraph, mixed $response): void
    {
        $paragraphId = $paragraph->getId();

        $this->header[$paragraphId] = $response;
    }

    protected function setShow(Paragraph $paragraph, bool $show): void
    {
        if (isset($this->show[$paragraph->getId()])) {
            return;
        }

        $this->show[$paragraph->getId()] = $show;
    }

    /**
     * @return mixed[]
     */
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

    private function setUrlAdmin(
        Paragraph $paragraph,
    ): string|\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return '';
        }

        $adminUrlGenerator = $this->adminUrlGenerator->setAction(Action::EDIT);
        $adminUrlGenerator->setEntityId($paragraph->getId());

        return $adminUrlGenerator->setController(ParagraphCrudController::class);
    }
}
