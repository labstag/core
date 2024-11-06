<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class AdminBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block, array $data)
    {
        $url = $this->setUrl($data['entity']);
        if (is_null($url)) {
            return null;
        }

        return $this->render(
            $view,
            [
                'url'   => $url->generateUrl(),
                'block' => $block,
                'data'  => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Block $block): iterable
    {
        unset($block);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Admin';
    }

    #[Override]
    public function getType(): string
    {
        return 'admin';
    }

    protected function setUrl($entity)
    {
        $controller = $this->siteService->getCrudController($entity::class);
        if (is_null($controller)) {
            return null;
        }

        $adminUrlGenerator = $this->adminUrlGenerator->setAction(Action::EDIT);
        $adminUrlGenerator->setEntityId($entity->getId());

        return $adminUrlGenerator->setController($controller);
    }
}
