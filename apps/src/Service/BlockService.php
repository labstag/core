<?php

namespace Labstag\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Labstag\Controller\Admin\BlockCrudController;
use Labstag\Entity\Block;
use Labstag\Repository\BlockRepository;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class BlockService
{

    private array $init = [];

    public function __construct(
        /**
         * @var iterable<\Labstag\Block\BlockAbstract>
         */
        #[AutowireIterator('labstag.blocks')]
        private readonly iterable $blocks,
        private AdminUrlGenerator $adminUrlGenerator,
        private Security $security,
        private AuthorizationCheckerInterface $authorizationChecker,
        private BlockRepository $blockRepository,
    )
    {
    }

    public function content(string $view, Block $block): ?Response
    {
        $content = null;
        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            $content = $row->content($view, $block);

            break;
        }

        return $content;
    }

    /**
     * @param mixed[] $blocks
     * @param mixed[] $data
     *
     * @return array{templates: mixed, block: mixed}[]
     */
    public function generate(array $blocks, array $data, bool $disable): array
    {
        $tab = [];
        foreach ($blocks as $block) {
            if (!$this->acces($block)) {
                continue;
            }

            $this->setContents($block, $data, $disable);

            $tab[] = [
                'templates' => $this->templates($block, 'content'),
                'block'     => $block,
            ];
        }

        return $tab;
    }

    /**
     * @return mixed[]
     */
    public function getAll(mixed $entity): array
    {
        $blocks = [];
        foreach ($this->blocks as $block) {
            $type  = $block->getType();
            $name  = $block->getName();
            if ($block->isEnable() || is_null($entity)) {
                $blocks[$name] = $type;
            }
        }

        return $blocks;
    }

    public function getBlock(?string $idBlock): ?object
    {
        $block  = $this->blockRepository->find($idBlock);
        if (!$block instanceof Block) {
            return null;
        }

        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            return $row;
        }

        return null;
    }

    public function getByCode(?string $code): ?object
    {
        foreach ($this->blocks as $block) {
            if ($block->getType() == $code) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $blocks
     */
    public function getContents(array $blocks): stdClass
    {
        $data         = new stdClass();
        $data->header = [];
        $data->footer = [];
        foreach ($blocks as $block) {
            $header = $this->getHeader($block['block']);
            $footer = $this->getFooter($block['block']);
            if (is_array($header)) {
                $data->header = array_merge($data->header, $header);
            } elseif ($header instanceof Response) {
                $data->header[] = $header;
            }

            if (is_array($footer)) {
                $data->footer = array_merge($data->footer, $footer);
            } elseif ($footer instanceof Response) {
                $data->footer[] = $footer;
            }
        }

        $data->header = array_filter($data->header, fn ($row): bool => !is_null($row));

        $data->footer = array_filter($data->footer, fn ($row): bool => !is_null($row));

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function getFields(?object $block, string $pageName): mixed
    {
        if (!$block instanceof Block) {
            return [];
        }

        $fields = [];
        foreach ($this->blocks as $row) {
            if ($row->getClass() == $block::class) {
                $fields = iterator_to_array($row->getFields($block, $pageName));

                break;
            }
        }

        return $fields;
    }

    public function getName(?Block $block): string
    {
        if (!$block instanceof Block) {
            return '';
        }

        $name = '';
        foreach ($this->blocks as $row) {
            if ($row->getClass() == $block::class) {
                $name = $row->getName();

                break;
            }
        }

        return $name;
    }

    /**
     * @return mixed[]
     */
    public function getRegions(): array
    {
        return [
            'header' => 'header',
            'footer' => 'footer',
            'main'   => 'main',
        ];
    }

    public function getType(?Block $block): string
    {
        $type = '';
        if (!($block instanceof Block)) {
            return $type;
        }

        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            $type = $row->getType();

            break;
        }

        return $type;
    }

    public function getUrlAdmin(Block $block): ?AdminUrlGeneratorInterface
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return null;
        }

        $adminUrlGenerator = $this->adminUrlGenerator->setAction(Action::EDIT);
        $adminUrlGenerator->setEntityId($block->getId());

        return $adminUrlGenerator->setController(BlockCrudController::class);
    }

    public function update(Block $block): void
    {
        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            $row->update($block);

            break;
        }
    }

    private function acces(Block $block): bool
    {
        $roles = $block->getRoles();
        if (is_null($roles) || [] === $roles) {
            return true;
        }

        return array_any($roles, $this->isGranted(...));
    }

    private function getFooter(Block $block): mixed
    {
        $footer = null;
        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            $footer = $row->getFooter($block);

            break;
        }

        return $footer;
    }

    private function getHeader(Block $block): mixed
    {
        $header = null;
        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            $header = $row->getHeader($block);

            break;
        }

        return $header;
    }

    private function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    /**
     * @param mixed[] $data
     */
    private function setContents(?Block $block, array $data, bool $disable): void
    {
        if (!$block instanceof Block) {
            return;
        }

        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            if (isset($this->init[$block->getId()])) {
                return;
            }

            $this->init[$block->getId()] = true;

            $row->generate($block, $data, $disable);

            break;
        }
    }

    /**
     * @return mixed[]|null
     */
    private function templates(Block $block, string $type): ?array
    {
        $template = null;
        foreach ($this->blocks as $row) {
            if ($block::class != $row->getClass()) {
                continue;
            }

            $template = $row->templates($block, $type);

            break;
        }

        return $template;
    }
}
