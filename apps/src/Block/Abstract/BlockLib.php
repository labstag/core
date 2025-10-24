<?php

namespace Labstag\Block\Abstract;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Block\Processors\LinkUrlProcessor;
use Labstag\Entity\Block;
use Labstag\Interface\BlockInterface;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;
use Labstag\Service\ConfigurationService;
use Labstag\Service\CrudAdminService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

#[AutoconfigureTag('labstag.blocks')]
abstract class BlockLib extends AbstractController implements BlockInterface
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
     * @var mixed[]
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
        /**
         * @var iterable<\Labstag\Data\Abstract\DataLib>
         */
        #[AutowireIterator('labstag.datas')]
        protected iterable $dataLibs,
        protected LinkUrlProcessor $linkUrlProcessor,
        protected LoggerInterface $logger,
        protected Security $security,
        protected RouterInterface $router,
        protected ConfigurationService $configurationService,
        protected AdminUrlGenerator $adminUrlGenerator,
        protected ParagraphService $paragraphService,
        protected CrudAdminService $crudAdminService,
        protected SiteService $siteService,
        protected SlugService $slugService,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager,
        protected Environment $twigEnvironment,
    )
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getData(Block $block): ?array
    {
        $blockId = $block->getId();

        return $this->data[$blockId] ?? null;
    }

    public function getFields(Block $block, string $pageName): mixed
    {
        unset($block, $pageName);

        return [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFooter(Block $block): ?array
    {
        $blockId = $block->getId();

        return $this->footer[$blockId] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHeader(Block $block): ?array
    {
        $blockId = $block->getId();

        return $this->header[$blockId] ?? null;
    }

    public function isEnable(): bool
    {
        return true;
    }

    public function isShow(Block $block): bool
    {
        $blockId = $block->getId();

        return $this->show[$blockId] ?? true;
    }

    /**
     * @return mixed[]
     */
    public function templates(Block $block, string $type): array
    {
        unset($block);

        return $this->getTemplateContent($type, $this->getType());
    }

    public function update(Block $block): void
    {
        unset($block);
    }

    /**
     * @return mixed[]
     */
    public function useIn(): array
    {
        return [];
    }

    /**
     * @return ServiceEntityRepositoryLib<object>
     */
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
            'blocks/' . $folder . '/' . $type . $htmltwig,
            'blocks/' . $folder . '/default' . $htmltwig,
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
            'hook'  => 'block',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates[$folder][$type];
    }

    /**
     * @param mixed[] $data
     */
    protected function setData(Block $block, array $data): void
    {
        $this->setShow($block, true);

        $data['configuration'] = $this->configurationService->getConfiguration();

        $this->data[$block->getId()] = $data;
    }

    /**
     * @param mixed[] $data
     */
    protected function setFooter(Block $block, array $data): void
    {
        $blockId = $block->getId();
        if ([] === $data) {
            return;
        }

        $this->footer[$blockId] = $data;
    }

    /**
     * @param mixed[] $data
     */
    protected function setHeader(Block $block, array $data): void
    {
        $blockId = $block->getId();
        if ([] === $data) {
            return;
        }

        $this->header[$blockId] = $data;
    }

    protected function setShow(Block $block, bool $show): void
    {
        if (isset($this->show[$block->getId()])) {
            return;
        }

        $this->show[$block->getId()] = $show;
    }
}
