<?php

namespace Labstag\Service;

use InvalidArgumentException;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RequestStack;

final class SlugService
{

    /**
     * @var array<string, mixed>
     */
    private array $types = [];

    public function __construct(
        #[AutowireIterator('labstag.datas')]
        private readonly iterable $datalibs,
        private PageRepository $pageRepository,
        private RequestStack $requestStack,
    )
    {
    }

    public function forEntity(object $entity): string
    {
        foreach ($this->datalibs as $datalib) {
            if ($datalib->supportsData($entity)) {
                return $datalib->generateSlug($entity);
            }
        }

        throw new InvalidArgumentException(sprintf('Unsupported entity type: %s', get_debug_type($entity)));
    }

    public function getEntity(): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        $slug    = $request->attributes->get('slug');

        return $this->getEntityBySlug($slug);
    }

    public function getEntityBySlug(?string $slug): ?object
    {
        foreach ($this->datalibs as $datalib) {
            $classe = new ReflectionClass($datalib);
            if ($datalib->match($slug) && $classe->hasMethod('getEntity')) {
                return $datalib->getEntity($slug);
            }
        }

        return null;
    }

    public function getPageByType(string $type): ?Page
    {
        $types = $this->getPageByTypes();

        return $types[$type] ?? null;
    }

    /**
     * @return mixed[]
     */
    private function getPageByTypes(): array
    {
        if ([] !== $this->types) {
            return $this->types;
        }

        $types = [];
        $data  = PageEnum::cases();
        foreach ($data as $row) {
            if ($row->value == PageEnum::PAGE->value) {
                continue;
            }

            $types[$row->value] = $this->pageRepository->getOneByType($row->value);
        }

        $this->types = $types;

        return $types;
    }
}
