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

    public function content(
        string $view,
        Block $block
    )
    {
        $content = null;
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $content = $row->content($view, $block);

            break;
        }

        return $content;
    }

    public function generate(array $blocks, array $data)
    {
        $tab = [];
        foreach ($blocks as $block) {
            if (!$this->acces($block)) {
                continue;
            }

            $this->setData($block, $data);

            $tab[] = [
                'templates' => $this->templates('content', $block),
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

    public function getContents($blocks, $methods)
    {
        $content = [];
        foreach ($blocks as $block) {
            $content = array_merge(
                $content,
                call_user_func_array([$this, $methods], [$block['block']])
            );
        }

        return array_filter(
            $content,
            fn($row) => !is_null($row)
        );
    }

    public function getFields($block, $pageName): array
    {
        if (!$block instanceof Block) {
            return [];
        }

        $type   = $block->getType();
        $fields = [];
        foreach ($this->blocks as $row) {
            if ($row->getType() == $type) {
                $fields = iterator_to_array($row->getFields($block, $pageName));

                break;
            }
        }

        return $fields;
    }

    public function getFooter(
        Block $block
    )
    {
        $footer = [];
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $footer[] = $row->getFooter($block);

            break;
        }

        return $footer;
    }

    public function getHeader(
        Block $block
    )
    {
        $header = [];
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $header[] = $row->getHeader($block);

            break;
        }

        return $header;
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

    public function setData(
        ?Block $block,
        array $data
    )
    {
        if (is_null($block)) {
            return;
        }

        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $row->setData($block, $data);

            break;
        }
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

    private function templates(string $type, Block $block)
    {
        $template = null;
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $template = $row->templates($type);

            break;
        }

        return $template;
    }
}
