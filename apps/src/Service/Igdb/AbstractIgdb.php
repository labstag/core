<?php

namespace Labstag\Service\Igdb;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\IgdbApi;
use Labstag\Service\FileService;

abstract class AbstractIgdb
{
    public function __construct(
        protected IgdbApi $igdbApi,
        protected EntityManagerInterface $entityManager,
        protected FileService $fileService,
    )
    {
    }
}
