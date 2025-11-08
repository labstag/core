<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Episode;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EpisodeData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected SeasonData $seasonData,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
        protected Security $security,
        protected RouterInterface $router,
    )
    {
        parent::__construct($fileService, $configurationService, $entityManager, $requestStack, $translator, $security, $router);
    }

    #[\Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = parent::asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return $this->seasonData->asset($entity->getRefseason(), $field);
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('episode');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->seasonData->placeholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Episode;
    }
}
