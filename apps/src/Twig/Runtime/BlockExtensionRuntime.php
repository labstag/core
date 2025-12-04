<?php

namespace Labstag\Twig\Runtime;

use Labstag\Entity\Block;
use Labstag\Service\BlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class BlockExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected BlockService $blockService,
        protected TranslatorInterface $translator,
    )
    {
        // Inject dependencies if needed
    }

    /**
     * @return array<string, mixed>
     */
    public function getContextMenu(Block $block): array
    {
        $urlAdmin = $this->blockService->getUrlAdmin($block);
        $data     = [
            'id'    => $this->getId($block),
            'class' => $this->getClass($block),
        ];
        if (is_null($urlAdmin)) {
            return $data;
        }

        $data['data-context_url']  = $urlAdmin;
        $data['data-context_text'] = $this->translator->trans(
            new TranslatableMessage('Update block (%name%) #%type%'),
            [
                '%name%' => $this->blockService->getName($block),
                '%type%' => $this->blockService->getType($block),
            ]
        );

        return $data;
    }

    /**
     * @param mixed[] $tab
     */
    public function getShow(array $tab): string|false|null
    {
        if (!isset($tab['templates']['view'])) {
            return null;
        }

        $content = $this->blockService->content($tab['templates']['view'], $tab['block']);

        if (!$content instanceof Response) {
            return null;
        }

        return $content->getContent();
    }

    public function name(object $object): string
    {
        if (!$object instanceof Block) {
            return '';
        }

        return $this->blockService->getName($object);
    }

    public function type(object $object): string
    {
        if (!$object instanceof Block) {
            return '';
        }

        return $this->blockService->getType($object);
    }

    private function getClass(Block $block): string
    {
        $type = $this->blockService->getType($block);
        $tab  = ['block', 'block_' . $type];

        $classes = explode(' ', (string) $block->getClasses());

        $tab = array_merge($tab, $classes);

        return trim(implode(' ', $tab));
    }

    private function getId(Block $block): string
    {
        $type = $this->blockService->getType($block);

        return 'block_' . $type . '-' . $block->getId();
    }
}
