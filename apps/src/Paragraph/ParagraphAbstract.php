<?php

namespace Labstag\Paragraph;

use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Labstag\Controller\Admin\ParagraphCrudController;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ServiceEntityRepositoryAbstract;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\FormService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SitemapService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Twig\Environment;
use Vich\UploaderBundle\Form\Type\VichImageType;

#[AutoconfigureTag('labstag.paragraphs')]
abstract class ParagraphAbstract extends AbstractController
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
        protected SlugService $slugService,
        protected ConfigurationService $configurationService,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function addFieldImageUpload(string $type, string $pageName): TextField|ImageField
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $textField = TextField::new($type . 'File', new TranslatableMessage('Image'));
            $textField->setFormType(VichImageType::class);
            $deleteLabel      = new TranslatableMessage('Delete image');
            $downloadLabel    = new TranslatableMessage('Download');
            $mimeTypesMessage = new TranslatableMessage('Please upload a valid image (JPEG, PNG, GIF, WebP).');
            $maxSizeMessage   = new TranslatableMessage(
                'The file is too large. Its size should not exceed {{ limit }}.'
            );
            $textField->setFormTypeOptions(
                [
                    'required'       => false,
                    'allow_delete'   => true,
                    'delete_label'   => $deleteLabel->__toString(),
                    'download_label' => $downloadLabel->__toString(),
                    'download_uri'   => true,
                    'image_uri'      => true,
                    'asset_helper'   => true,
                    'constraints'    => [
                        new File(
                            [
                                'maxSize'          => ini_get('upload_max_filesize'),
                                'mimeTypes'        => [
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                    'image/webp',
                                ],
                                'mimeTypesMessage' => $mimeTypesMessage->__toString(),
                                'maxSizeMessage'   => $maxSizeMessage->__toString(),
                            ]
                        ),
                    ],
                ]
            );

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

    /**
     * @return array<string>
     */
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

    public function isShow(Paragraph $paragraph): bool
    {
        $paragraphId = $paragraph->getId();

        return $this->show[$paragraphId] ?? true;
    }

    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return in_array($object::class, $this->useIn());
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

    protected function getOEmbedUrl(string $html): ?string
    {
        $domDocument = new DOMDocument();
        $domDocument->loadHTML($html);

        $domNodeList = $domDocument->getElementsByTagName('iframe');
        if (0 === count($domNodeList)) {
            return null;
        }

        $iframe = $domNodeList->item(0);

        return $iframe->getAttribute('src');
    }

    /**
     * @return PaginationInterface<int, mixed>
     */
    protected function getPaginator(mixed $query, ?int $limit): PaginationInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->paginator->paginate($query, $request->attributes->getInt('page', 1), $limit);
    }

    /**
     * @return ServiceEntityRepositoryAbstract<object>
     */
    protected function getRepository(string $entity): ServiceEntityRepositoryAbstract
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryAbstract) {
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
        $data['configuration'] = $this->configurationService->getConfiguration();

        $this->data[$paragraph->getId()] = $data;
    }

    protected function setFooter(Paragraph $paragraph, mixed $response): void
    {
        $paragraphId = $paragraph->getId();

        $this->footer[$paragraphId] = $response;
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

    private function setUrlAdmin(Paragraph $paragraph): string|AdminUrlGeneratorInterface
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return '';
        }

        $adminUrlGenerator = $this->adminUrlGenerator->setAction(Action::EDIT);
        $adminUrlGenerator->setEntityId($paragraph->getId());

        return $adminUrlGenerator->setController(ParagraphCrudController::class);
    }
}
