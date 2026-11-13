<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Entity\Block;
use Labstag\Repository\BlockRepository;
use Labstag\Service\BlockService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlockActionService
{
    public function __construct(
        private BlockRepository $blockRepository,
        private BlockService $blockService,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function getBlocksOrdered(): array
    {
        $queryBuilder = $this->blockRepository->createQueryBuilder('b');
        $this->blockRepository->findAllOrderedByRegion($queryBuilder);
        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'block-position');

        return $query->getResult();
    }

    public function newBlock(): array
    {
        return $this->blockService->getAll(null);
    }

    public function processPositionBlock(array $allTypes, array $request, BlockRepository $repository): void
    {
        foreach ($allTypes as $allType) {
            $data = $request[$allType] ?? null;
            if (is_null($data)) {
                continue;
            }

            foreach ($data as $id => $position) {
                $entity = $repository->find($id);
                if (!$entity instanceof Block) {
                    continue;
                }

                $entity->setPosition($position);
                $repository->persist($entity);
            }
        }

        $repository->flush();
    }

    public function redirectToIndex(): RedirectResponse
    {
        $url = $this->urlGenerator->generate('admin_block_index');

        return new RedirectResponse($url);
    }
}
