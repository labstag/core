<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;
use Symfony\Component\HttpFoundation\Response;

class AdminBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block): ?Response
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render($view, $this->getData($block));
    }

    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Block $block, array $data, bool $disable): void
    {
        unset($disable);
        $url = $this->setUrl($data['entity']);
        if (!$url instanceof AdminUrlGeneratorInterface) {
            $this->setShow($block, false);

            return;
        }

        $this->setData(
            $block,
            [
                'url'   => $url->generateUrl(),
                'block' => $block,
                'data'  => $data,
            ]
        );
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

    protected function setUrl(object $entity): ?AdminUrlGeneratorInterface
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
