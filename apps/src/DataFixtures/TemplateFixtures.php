<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Template;
use Override;

class TemplateFixtures extends FixtureAbstract
{
    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $data      = $this->data();
        $generator = $this->setFaker();
        foreach ($data as $key => $title) {
            $template = new Template();
            $template->setCode($key);
            $content = $generator->unique()->text(200);
            $template->setText($content);
            $template->setHtml($content);
            $template->setTitle($title);
            $objectManager->persist($template);
        }

        $templates = $this->emailService->all();

        foreach ($templates as $row) {
            $template = new Template();
            $template->setCode($row->getType());
            $template->setText($row->setText());
            $template->setHtml($row->setHtml());
            $template->setTitle($row->getName());
            $objectManager->persist($template);
        }

        $objectManager->flush();
    }

    /**
     * @return mixed[]
     */
    private function data(): array
    {
        return [
            'checknew_address' => 'Ajout nouvelle adresse',
            'checknew_phone'   => 'Ajout nouveau numéro de téléphone',
            'checknew_link'    => 'Ajout nouvelle url',
        ];
    }
}
