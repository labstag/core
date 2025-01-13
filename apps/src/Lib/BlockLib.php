<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Block;
use Labstag\Interface\BlockInterface;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

abstract class BlockLib extends AbstractController implements BlockInterface
{

    protected array $data = [];

    protected array $footer = [];

    protected array $header = [];

    protected array $show = [];

    protected array $templates = [];

    public function __construct(
        protected RouterInterface $router,
        protected AdminUrlGenerator $adminUrlGenerator,
        protected ParagraphService $paragraphService,
        protected SiteService $siteService,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function getData(Block $block): ?array
    {
        $blockId = $block->getId();

        return $this->data[$blockId] ?? null;
    }

    public function getFields(Block $block, string $pageName): iterable
    {
        unset($block, $pageName);

        return [];
    }

    public function getFooter(Block $block): ?array
    {
        $blockId = $block->getId();

        return $this->footer[$blockId] ?? null;
    }

    public function getHeader(Block $block): ?array
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

    public function isShow(Block $block): bool
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

    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        return $this->entityManager->getRepository($entity);
    }

    protected function getTemplateContent(string $folder, string $type): array
    {
        if (isset($this->templates[$type])) {
            return $this->templates[$type];
        }

        $htmltwig = '.html.twig';
        $files = [
            'blocks/' . $folder . '/' . $type . $htmltwig,
            'blocks/' . $folder . '/default' . $htmltwig,
        ];

        $view = end($files);
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

    protected function setData(Block $block, array $data): void
    {
        $this->setShow($block, true);

        $data['configuration'] = $this->siteService->getConfiguration();

        $this->data[$block->getId()] = $data;
    }

    protected function setFooter(Block $block, array $data): void
    {
        $blockId = $block->getId();

        $this->footer[$blockId] = $data;
    }

    protected function setHeader(Block $block, array $data): void
    {
        $blockId = $block->getId();

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
