<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Template;
use Labstag\Lib\FixtureLib;
use Labstag\Service\UserService;
use Override;

class TemplateFixtures extends FixtureLib
{
    public function __construct(
        protected UserService $userService
    )
    {
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $data = $this->data();
        foreach ($data as $key => $title) {
            $template = new Template();
            $template->setCode($key);
            $template->setTitle($title);
            $objectManager->persist($template);
        }

        $objectManager->flush();
    }

    private function data(): array
    {
        return array_merge(
            [
                'checknew_address' => 'Ajout nouvelle adresse',
                'checknew_phone'   => 'Ajout nouveau numéro de téléphone',
                'checknew_link'    => 'Ajout nouvelle url',
            ],
            $this->userService->getTemplates(),
        );
    }
}
