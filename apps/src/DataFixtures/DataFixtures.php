<?php

namespace Labstag\DataFixtures;

use Override;
use Doctrine\Persistence\ObjectManager;
use Labstag\Lib\FixtureLib;

class DataFixtures extends FixtureLib
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        unset($objectManager);
        $this->fileService->deleteAll();
    }
}
