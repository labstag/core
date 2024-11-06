<?php

namespace Labstag\Service;

use Labstag\Entity\Block;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BlockService
{
    public function __construct(
        #[AutowireIterator('labstag.blocks')]
        private readonly iterable $blocks,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected TokenStorageInterface $tokenStorage
    )
    {
    }

    public function generate(array $blocks)
    {
        $tab = [];
        foreach ($blocks as $block) {
            if (!$this->acces($block)) {
                continue;
            }

            $tab[] = [
                'templates' => $this->templates($block),
                'block'     => $block,
            ];
        }

        return $tab;
    }

    public function getAll($entity): array
    {
        $blocks = [];
        foreach ($this->blocks as $block) {
            $inUse = $block->useIn();
            $type  = $block->getType();
            $name  = $block->getName();
            if ((in_array($entity, $inUse) && $block->isEnable()) || is_null($entity)) {
                $blocks[$name] = $type;
            }
        }

        return $blocks;
    }

    public function getFields($block): array
    {
        if (!$block instanceof Block) {
            return [];
        }

        $type   = $block->getType();
        $fields = [];
        foreach ($this->blocks as $row) {
            if ($row->getType() == $type) {
                $fields = iterator_to_array($row->getFields($block));

                break;
            }
        }

        return $fields;
    }

    public function getNameByCode($code)
    {
        $name = '';
        foreach ($this->blocks as $block) {
            if ($block->getType() == $code) {
                $name = $block->getName();

                break;
            }
        }

        return $name;
    }

    public function getRegions(): array
    {
        return [
            'header' => 'header',
            'footer' => 'footer',
            'main'   => 'main',
        ];
    }

    // TODO : show content
    public function showContent(
        string $view,
        Block $block,
        array $data
    )
    {
        $content = null;
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $content = $row->content($view, $block, $data);

            break;
        }

        return $content;
    }

    protected function acces($block)
    {
        $roles = $block->getRoles();
        if (is_null($roles) || 0 == count($roles)) {
            return true;
        }

        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    private function templates(Block $block)
    {
        $template = null;
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $template = $row->templates();

            break;
        }

        return $template;
    }
}
