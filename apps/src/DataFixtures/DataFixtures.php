<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Lib\FixtureLib;
use Override;

class DataFixtures extends FixtureLib
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        unset($objectManager);
        $this->fileService->deleteAll();
    }
}
