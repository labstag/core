<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Platform;
use Labstag\Service\Igdb\GameService;
use Labstag\Service\Igdb\PlatformService;

final class IgdbService
{
    public function __construct(
        private PlatformService $platformService,
        private GameService $gameService,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function games(): GameService
    {
        return $this->gameService;
    }

    /**
     * @return mixed[]
     */
    public function getPlatformChoices(): array
    {
        $entityRepository = $this->entityManager->getRepository(Platform::class);
        $platforms        = $entityRepository->findBy(
            [],
            ['title' => 'ASC']
        );
        $choices    = [];
        foreach ($platforms as $platform) {
            $choices[$platform->getTitle()] = $platform->getId();
        }

        return $choices;
    }

    public function platforms(): PlatformService
    {
        return $this->platformService;
    }
}
