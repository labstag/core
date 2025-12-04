<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Meta;
use Labstag\Repository\BlockRepository;
use ReflectionClass;
use Twig\Environment;

final class ViewResolverService
{
    /**
     * @var array<string, mixed>
     */
    private array $requestCache = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConfigurationService $configurationService,
        private BlockService $blockService,
        private BlockRepository $blockRepository,
        private Environment $twigEnvironment,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getDataByEntity(object $entity, bool $disable = false): array
    {
        $cacheKey = 'data:' . spl_object_hash($entity) . ':' . ($disable ? '1' : '0');
        if (isset($this->requestCache[$cacheKey])) {
            return $this->requestCache[$cacheKey];
        }

        $reflectionClass = new ReflectionClass($entity);
        $data            = [
            'entity'     => $entity,
            'paragraphs' => $entity->getParagraphs()
                ->getValues(),
        ];

        $data['img'] = $reflectionClass->hasMethod('getImg') ? $entity->getImg() : $entity->getPoster();

        if (method_exists($entity, 'getTags')) {
            $data['tags'] = $entity->getTags();
        }

        if (method_exists($entity, 'getCategories')) {
            $data['categories'] = $entity->getCategories();
        }

        [
            $header,
            $main,
            $footer,
        ]         = $this->getBlocks($data, $disable);
        $blocks   = array_merge($header, $main, $footer);
        $contents = $this->blockService->getContents($blocks);

        $blocks = [
            'header' => $header,
            'main'   => $main,
            'footer' => $footer,
        ];
        foreach ($blocks as $key => $value) {
            if (0 !== count($value)) {
                continue;
            }

            unset($blocks[$key]);
        }

        $meta = $entity->getMeta();
        if (!$meta instanceof Meta) {
            $meta = new Meta();
            $entity->setMeta($meta);
            $this->entityManager->getRepository($entity::class)->save($entity);
        }

        return $this->requestCache[$cacheKey] = [
            'meta'   => $this->getMetaByEntity($meta),
            'blocks' => $blocks,
            'header' => $contents->header,
            'footer' => $contents->footer,
            'config' => $this->configurationService->getConfiguration(),
            'data'   => $data,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataViewByEntity(object $entity): array
    {
        $data = $this->getDataByEntity($entity, false);
        $view = $this->getViewByEntity($entity);

        return [
            'data' => $data,
            'view' => $view,
        ];
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function getBlocks(array $data, bool $disable): array
    {
        $queryBuilder = $this->blockRepository->createQueryBuilder('b');
        $this->blockRepository->findAllOrderedByRegion($queryBuilder);
        $query        = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'block-position');

        $blocks = $query->getResult();
        $header = [];
        $main   = [];
        $footer = [];

        foreach ($blocks as $block) {
            if ('header' == $block->getRegion()) {
                $header[] = $block;
            } elseif ('main' == $block->getRegion()) {
                $main[] = $block;
            } elseif ('footer' == $block->getRegion()) {
                $footer[] = $block;
            }
        }

        return [
            $this->blockService->generate($header, $data, $disable),
            $this->blockService->generate($main, $data, $disable),
            $this->blockService->generate($footer, $data, $disable),
        ];
    }

    private function getMetaByEntity(Meta $meta): Meta
    {
        return $meta;
    }

    private function getViewByEntity(object $entity): string
    {
        $cacheKey = 'view:' . spl_object_hash($entity);
        if (isset($this->requestCache[$cacheKey])) {
            return $this->requestCache[$cacheKey];
        }

        $reflectionClass = new ReflectionClass($entity);
        $entityName      = ucfirst($reflectionClass->getShortName());

        return $this->requestCache[$cacheKey] = $this->getViewByEntityName($entityName);
    }

    private function getViewByEntityName(string $entityName): string
    {
        $loader = $this->twigEnvironment->getLoader();
        $files  = ['views/' . $entityName . '.html.twig', 'views/default.html.twig'];

        foreach ($files as $file) {
            if ($loader->exists($file)) {
                return $file;
            }
        }

        return end($files);
    }
}
